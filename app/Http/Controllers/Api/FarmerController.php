<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use Illuminate\Http\Request;

class FarmerController extends Controller
{
    // Lister tous les fermiers
    public function index()
    {
        $farmers = Farmer::all();
        return response()->json($farmers);
    }

    // Créer un fermier
    public function store(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string|unique:farmers',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'phone' => 'required|string|unique:farmers',
            'credit_limit_fcfa' => 'nullable|numeric|min:0',
        ]);

        $farmer = Farmer::create($request->all());
        return response()->json($farmer, 201);
    }

    // Rechercher un fermier par identifiant ou téléphone
    public function search(Request $request)
    {
        $query = $request->query('q');

        $farmer = Farmer::where('identifier', $query)
            ->orWhere('phone', $query)
            ->first();

        if (!$farmer) {
            return response()->json(['message' => 'Fermier non trouvé'], 404);
        }

        $farmer->total_debt = $farmer->totalDebt();
        return response()->json($farmer);
    }

    // Afficher un fermier avec résumé de dettes
    public function show($id)
    {
        $farmer = Farmer::with([
            'debts' => function($query) {
                $query->whereIn('status', ['open', 'partial']);
            }
        ])->findOrFail($id);

        $farmer->total_debt = $farmer->totalDebt();
        return response()->json($farmer);
    }

    // Modifier un fermier
    public function update(Request $request, $id)
    {
        $request->validate([
            'firstname' => 'sometimes|string|max:255',
            'lastname' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|unique:farmers,phone,'.$id,
            'credit_limit_fcfa' => 'sometimes|numeric|min:0',
        ]);

        $farmer = Farmer::findOrFail($id);
        $farmer->update($request->all());
        return response()->json($farmer);
    }
}