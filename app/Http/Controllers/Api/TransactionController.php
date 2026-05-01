<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Debt;
use App\Models\Farmer;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'farmer_id' => 'required|exists:farmers,id',
            'payment_method' => 'required|in:cash,credit',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $farmer = Farmer::findOrFail($request->farmer_id);

        // Calcul du total
        $total = 0;
        $itemsData = [];

        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $subtotal = $product->price_fcfa * $item['quantity'];
            $total += $subtotal;
            $itemsData[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price_fcfa' => $product->price_fcfa,
            ];
        }

        // Calcul des intérêts si crédit
        $interestRate = 0;
        $totalWithInterest = $total;

        if ($request->payment_method === 'credit') {
            $interestRate = Setting::getValue('interest_rate', 30);
            $totalWithInterest = $total * (1 + $interestRate / 100);

            // Vérification limite de crédit
            $currentDebt = $farmer->totalDebt();
            if (($currentDebt + $totalWithInterest) > $farmer->credit_limit_fcfa) {
                return response()->json([
                    'message' => 'Limite de crédit dépassée',
                    'current_debt' => $currentDebt,
                    'credit_limit' => $farmer->credit_limit_fcfa,
                    'transaction_amount' => $totalWithInterest,
                ], 422);
            }
        }

        // Créer la transaction
        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'farmer_id' => $farmer->id,
                'operator_id' => $request->user()->id,
                'total_fcfa' => $total,
                'payment_method' => $request->payment_method,
                'interest_rate' => $interestRate,
                'total_with_interest' => $totalWithInterest,
                'status' => 'completed',
            ]);

            // Créer les items
            foreach ($itemsData as $item) {
                $transaction->items()->create($item);
            }

            // Créer la dette si crédit
            if ($request->payment_method === 'credit') {
                Debt::create([
                    'transaction_id' => $transaction->id,
                    'farmer_id' => $farmer->id,
                    'amount_fcfa' => $totalWithInterest,
                    'remaining_fcfa' => $totalWithInterest,
                    'status' => 'open',
                ]);
            }

            DB::commit();

            return response()->json(
                $transaction->load('items.product', 'farmer', 'debt'),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur lors de la transaction', 'error' => $e->getMessage()], 500);
        }
    }

    // Lister les transactions d'un fermier
    public function index(Request $request)
    {
        $transactions = Transaction::with('items.product', 'farmer')
            ->when($request->farmer_id, fn($q) => $q->where('farmer_id', $request->farmer_id))
            ->latest()
            ->get();

        return response()->json($transactions);
    }

    // Afficher une transaction
    public function show($id)
    {
        $transaction = Transaction::with('items.product', 'farmer', 'operator', 'debt')
            ->findOrFail($id);
        return response()->json($transaction);
    }
}