<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Tontine;
use App\Models\Contribution;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ContributionController extends Controller
{
    public function contribute(Request $request, Tontine $tontine)
    {
        $user = auth()->user();
        $montant = round($tontine->montant / $tontine->nombre_personne, 2);

        // Créer la session de paiement via l'API Wave
       /*  $response = Http::withHeaders([
            "Authorization" => "Bearer " . env('WAVE_API_KEY'),
            "Content-Type" => "application/json"
        ])->post("https://api.wave.com/v1/checkout/sessions", [
                    "amount" => $montant,
                    "currency" => "XOF",
                   // "aggregated_merchant_id" => env('WAVE_AGGREGATED_MERCHANT_ID'), // Assurez-vous de définir cette clé dans votre .env
                    "error_url" => route('payment.error'),
                    "success_url" => route('payment.success'),
                ]);
        // Vérifier si la requête a échoué
        if ($response->failed()) {
            return response()->json(['error' => 'Erreur lors de la création de la session de paiement.'], 500);
        }

        $checkout_session = $response->json();

        // Vérifier si 'wave_launch_url' est présent dans la réponse
        if (!isset($checkout_session['wave_launch_url'])) {
            return response()->json(['error' => 'Réponse de l\'API Wave invalide.'], 500);
        } */

        // Utiliser une transaction pour sécuriser l'enregistrement des données
        DB::beginTransaction();

        try {
            // Créer la contribution
            $contribution = Contribution::create([
                'user_id' => $user->id,
                'tontine_id' => $tontine->id,
                'montant' => $montant,
            ]);

            DB::commit();

            return response()->json([
                /* 'wave_launch_url' => $checkout_session['wave_launch_url'],
                'transaction_id' => $checkout_session['transaction_id'] ?? null, 
                'amount' => $checkout_session['amount'],
                'currency' => $checkout_session['currency'], */

                'payement reussi !'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur lors de l\'enregistrement de la contribution.'], 500);
        }
    }

    public function success(Request $request)
    {

        return response()->json('Paiement réussi !');
    }

    public function error(Request $request)
    {

        return response()->json('Erreur de paiement !');
    }
}
