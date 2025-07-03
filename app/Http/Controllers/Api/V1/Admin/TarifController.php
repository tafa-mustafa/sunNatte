<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Tarif;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TarifController extends Controller
{
    public function index()
    {
        $tarifs = Tarif::all();
        return response()->json($tarifs, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:cotisation,retrait',
            'min' => 'nullable|numeric',
            'max' => 'nullable|numeric',
            'pourcentage' => 'required|numeric|min:0',
        ]);

        $tarif = Tarif::create($request->all());

        return response()->json([
            'message' => 'Tarif ajouté avec succès.',
            'tarif' => $tarif
        ], 201);
    }

    public function show(Tarif $tarif)
    {
        return response()->json($tarif);
    }

    public function update(Request $request, Tarif $tarif)
    {
        $request->validate([
            'min' => 'nullable|numeric',
            'max' => 'nullable|numeric',
            'pourcentage' => 'required|numeric|min:0',
        ]);

        $tarif->update($request->all());

        return response()->json([
            'message' => 'Tarif mis à jour avec succès.',
            'tarif' => $tarif
        ]);
    }

    
    public function destroy(Tarif $tarif)
    {
        $tarif->delete();

        return response()->json([
            'message' => 'Tarif supprimé avec succès.'
        ]);
    }
}
