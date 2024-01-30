<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use App\Models\{Tontine, User, Adhesion, Materiel};
use App\Http\Resources\TontineWithMembresResource;

class TontineController extends Controller
{

    public function index()
    {
        $utilisateur = auth()->user();

        // Récupérer les tontines associées à cet utilisateur
        $tontines = $utilisateur->tontines()->get();



        return response()->json($tontines);
    }


    public function store(Request $request)
    {


        $request->validate([
            'nom' => 'required',
            'nombre_personne' => 'required',
            'type' => 'required',
            'duree' => 'required',
            'montant' => 'required',
            'tirage' => 'required',
            'code_adhesion' => 'nullable|unique',
            'materiel_id' => 'nullable',

        ]);

        try {
            DB::beginTransaction();

            $codeAdhesion = rand(1111, 9999);

            $tontine = Tontine::create([
                'nom' => $request->nom,
                'nombre_personne' => $request->nombre_personne,
                'type' => $request->type,
                'duree' => $request->nombre_personne,
                'montant' => $request->montant,
                'tirage' => $request->tirage,
                'code_adhesion' => $codeAdhesion,
                'materiel_id' => $request->materiel_id,

            ]);

            $createur = Auth::user();

            $badge = "propio";

            $createur->update(['badge' => $badge]);
            $tontine->users()->attach($createur->id);




            DB::commit();

            return response()->json($tontine);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'Erreur lors de la création de la tontine.' . $e->getMessage()], 500);
        }
    }



    public function adhesion(Request $request, Tontine $tontine)
    {
        if ($tontine->users->count() >= $tontine->nombre_personne) {
            return response()->json(['message' => 'Le nombre maximum de participants est atteint.'], 422);
        }

        $codeAdhesion = $request->input('code_adhesion');

        // Vérifier si le code d'adhésion est correct
        
        if ($tontine->code_adhesion !== $codeAdhesion) {
            return response()->json(['message' => 'Code d\'adhésion incorrect.'], 422);
        }


        $users = auth()->user();
        if (!$tontine->users->contains($users)) {

            $tontine->users()->attach($users);
            // return new TontineWithMembresResource($tontine);
            return response()->json(['message' => 'Adhesion Fait'], 200);
        }

        return response()->json(['message' => 'Vous êtes déjà membre de cette tontine.'], 422);


    }
    public function show(Tontine $tontine)
    {

        $data = [
            'id' => $tontine->id,
            'nom' => $tontine->nom,
            'code adhesion' => $tontine->code_adhesion,
            'nombre_personne' => $tontine->nombre_personne,
            'nombre_membres_actuels' => $tontine->users->count(),
            'nombre_restant' => max(0, $tontine->nombre_personne - $tontine->users->count()),
            'duree' => $tontine->duree,
            'montan' => $tontine->montant,
            'materiel_image' => $tontine->materiel ? $tontine->materiel->image : null,
            'materiel_titre' => $tontine->materiel ? $tontine->materiel->nom : null,
            'materiel_id' => $tontine->materiel ? $tontine->materiel->id : null,

            'montant_mensuel' => round($tontine->montant / $tontine->nombre_personne, 2),
            'statut' => $tontine->statut,

            'membres' => $tontine->users->map(function ($users) {
                return [
                    'id' => $users->id,
                    'nom' => $users->nom,
                    'phone' => $users->phone,
                ];
            }),
        ];
        return response()->json($data);
    }


    public function show_mt(Materiel $materiel)
    {

        $data = $materiel->toArray();

        return response()->json($data);
    }




    public function tirage(Tontine $tontine)
    {

        $createur = auth()->user();

        // Vérifier si l'utilisateur actuel est le créateur de la tontine
        if ($createur->id !== $tontine->createur_id) {
            return response()->json(['message' => 'Seul le créateur de la tontine peut effectuer le tirage.'], 403);
        }

        $utilisateurs = $tontine->users->all();

        // Vérifier s'il y a au moins deux membres dans la tontine
        if (count($utilisateurs) < $tontine->nombre_personne) {
            return response()->json(['message' => 'Le tirage ne peut pas avoir lieu car les membres ne sont pas au complet.']);
        }

        shuffle($utilisateurs);

        return response()->json(['le tirage est' => $utilisateurs]);
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
