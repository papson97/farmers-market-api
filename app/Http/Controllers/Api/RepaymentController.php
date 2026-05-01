<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Repayment;
use App\Models\Debt;
use App\Models\Farmer;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RepaymentController extends Controller
{
    // Lister les dettes d'un fermier
    public function debts($farmerId)
    {
        $farmer = Farmer::findOrFail($farmerId);
        $debts = Debt::with('transaction')
            ->where('farmer_id', $farmerId)
            ->whereIn('status', ['open', 'partial'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'farmer' => $farmer,
            'total_debt' => $farmer->totalDebt(),
            'debts' => $debts,
        ]);
    }

    // Enregistrer un remboursement
    public function store(Request $request)
    {
        $request->validate([
            'farmer_id' => 'required|exists:farmers,id',
            'kg_received' => 'required|numeric|min:0.1',
        ]);

        $farmer = Farmer::findOrFail($request->farmer_id);

        // Taux configurable
        $commodityRate = Setting::getValue('commodity_rate', 1000);
        $totalFcfa = $request->kg_received * $commodityRate;

        // Récupérer les dettes ouvertes en ordre FIFO
        $debts = Debt::where('farmer_id', $farmer->id)
            ->whereIn('status', ['open', 'partial'])
            ->orderBy('created_at', 'asc')
            ->get();

        if ($debts->isEmpty()) {
            return response()->json(['message' => 'Aucune dette à rembourser'], 422);
        }

        DB::beginTransaction();
        try {
            $repayment = Repayment::create([
                'farmer_id' => $farmer->id,
                'operator_id' => $request->user()->id,
                'kg_received' => $request->kg_received,
                'commodity_rate_fcfa' => $commodityRate,
                'total_fcfa_credited' => $totalFcfa,
            ]);

            // Appliquer le remboursement FIFO
            $remaining = $totalFcfa;

            foreach ($debts as $debt) {
                if ($remaining <= 0) break;

                $applied = min($remaining, $debt->remaining_fcfa);
                $newRemaining = $debt->remaining_fcfa - $applied;

                // Mettre à jour la dette
                $debt->remaining_fcfa = $newRemaining;
                $debt->status = $newRemaining <= 0 ? 'closed' : 'partial';
                $debt->save();

                // Enregistrer le lien remboursement-dette
                $repayment->debts()->attach($debt->id, [
                    'amount_applied_fcfa' => $applied
                ]);

                $remaining -= $applied;
            }

            DB::commit();

            return response()->json(
                $repayment->load('debts', 'farmer'),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors du remboursement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Historique des remboursements d'un fermier
    public function index($farmerId)
    {
        $repayments = Repayment::with('debts')
            ->where('farmer_id', $farmerId)
            ->latest()
            ->get();

        return response()->json($repayments);
    }
}