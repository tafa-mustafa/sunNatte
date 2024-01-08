<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Tontine;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TontineController extends Controller
{
   
    public function index()
    {
        $tontine =  Tontine::all();

        return response()->json(['tontine' => $tontine]);

    }

    
    public function store(Request $request)
    {
        $request->validate([
        'nom' => 'required',
        'nombre_personne' => 'required',
        'type' => 'required',
        'date_debut' => 'required',
        'date_fin' => 'required',
        ]);

            $tontine = Tontine::create([
                'user_id' => auth()->id(),
                'nombre_personne' => $request->nombre_personne,
                'type' => $request->type,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
            ]);

                    return response()->json(['tontine' => $tontine]);

    }

   
    public function show(string $id)
    {
                $tontine= Tontine::find($id);
                 if (!$tontine) {
            return response()->json(['message' => 'tontine not found'], 404);
        }

        return response()->json(['tontine' => $tontine]);
    }

   
    public function update(Request $request, string $id)
    {
        //
    }

   
    public function destroy(string $id)
    {
        //
    }
}
