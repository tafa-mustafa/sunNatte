<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Tontine;
use App\Models\Contribution;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class ContributionController extends Controller
{
   public function contribute(Request $request, Tontine $tontine)
{
       $user = auth()->user();
    $montant = round($tontine->montant / $tontine->nombre_personne, 2);

    // Création du paiement avec Wave
    $response = Http::withHeaders([
        "Authorization" => "Bearer " . env('WAVE_API_KEY'),
        "Content-Type" => "application/json"
    ])->post("https://api.wave.com/v1/checkout/sessions", [
        "amount" => $montant,
        "currency" => "XOF",
        "error_url" => route('payment.error'), // Utilise la nouvelle route pour l'URL d'erreur
        "success_url" => route('payment.success'), // Utilise la nouvelle route pour l'URL de succès
    ]);

    if ($response->failed()) {
        return response()->json(['error' => 'Erreur lors de la création de la session de paiement.'], 500);
    } else {
        $checkout_session = $response->json();
        // Redirection vers la page de paiement de Wave
        if (isset($checkout_session['wave_launch_url'])) {
            Contribution::create([
                'user_id' => $user->id,
                'tontine_id' => $tontine->id,
                'montant' => $montant,
                // Ajoutez d'autres champs si nécessaire
            ]);

            // Personnalisation de la réponse avec les données de l'API Wave
            $responseData = [
                'wave_launch_url' => $checkout_session['wave_launch_url'],
                'transaction_id' => $checkout_session['transaction_id'],
                'payment_status' => $checkout_session['payment_status'],
                'amount' => $checkout_session['amount'],
                'currency' => $checkout_session['currency'],
                'when_completed' => $checkout_session['when_completed'],
                // Ajoutez d'autres données si nécessaire
            ];

            return response()->json($responseData);
        } else {
            return response()->json(['error' => 'Réponse de l\'API Wave invalide.'], 500);
        }
    }
}



    public function success(Request $request)
    {
        return response()->json('payement avec success !'); // Vous pouvez afficher une vue de confirmation de paiement réussi par exemple
    }

    public function error(Request $request)
    {
        return response()->json(' error payement  !'); // Vous pouvez afficher une vue de confirmation de paiement réussi par exemple
    }


}
