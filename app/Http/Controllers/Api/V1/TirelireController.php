<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Tirelire;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\TarifService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class TirelireController extends Controller
{
    public function createTirelire(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'montant' => 'required|numeric|min:1',
            'montant_objectif' => 'required|numeric|min:1',
            'objectif' => 'nullable|string|max:255',
            'date_fin' => 'required|date|after:today',
        ]);

        $user = auth()->user();

        // Créer une session de paiement via Wave
        $response = Http::withHeaders([
            "Authorization" => "Bearer " . env('WAVE_API_KEY'),
            "Content-Type" => "application/json"
        ])->post("https://api.wave.com/v1/checkout/sessions", [
            "amount" => $request->montant,
            "currency" => "XOF",
            "error_url" => route('payment.error'),
            "success_url" => route('payment.success', [], true), // URL absolue
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Erreur lors de la création de la session de paiement.'], 500);
        }

        $checkout_session = $response->json();
        $session_id = $checkout_session['id'] ?? null;
        $payment_url = $checkout_session['wave_launch_url'] ?? null;

        if (!$session_id || !$payment_url) {
            return response()->json(['error' => 'Session de paiement invalide.'], 500);
        }

        // Créer une tirelire en statut "pending"
        $tirelire = Tirelire::create([
            'titre' => $request->titre,
            'montant' => 0, // Initialement 0, le montant sera ajouté après paiement
            'objectif' => $request->objectif,
            'montant_objectif' => $request->montant_objectif,
            'date_debut' => now(),
            'date_fin' => $request->date_fin,
            'user_id' => $user->id,
        ]);

        // Sauvegarder la transaction
        Transaction::create([
            'tirelire_id' => $tirelire->id,
            'session_id' => $session_id,
            'montant' => $request->montant,
            'statut' => 'pending',
        ]);

        return response()->json([
            'message' => 'Session de paiement créée avec succès.',
            'payment_url' => $payment_url,
            'tirelire' => $tirelire,
        ]);
    }

    public function addMoneyToTirelire(Request $request, Tirelire $tirelire)
    {
        $request->validate([
            'montant' => 'required|numeric|min:1',
        ]);

        $user = auth()->user();

        if ($tirelire->user_id !== $user->id) {
            return response()->json(['error' => 'Vous n\'êtes pas autorisé à modifier cette tirelire.'], 403);
        }

        $montant = $request->montant;
        $commission = $montant * 0.02;
        $montantNet = $montant + $commission;

        // Créer la session Wave
        $response = Http::withHeaders([
            "Authorization" => "Bearer " . env('WAVE_API_KEY'),
            "Content-Type" => "application/json"
        ])->post("https://api.wave.com/v1/checkout/sessions", [
            "amount" => $montantNet,
            "currency" => "XOF",
            "error_url" => route('payment.error', [], true),
            "success_url" => route('payment.success', [], true),
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Erreur lors de la création de la session de paiement.'], 500);
        }

        $checkout_session = $response->json();
        $session_id = $checkout_session['id'] ?? null;
        $payment_url = $checkout_session['wave_launch_url'] ?? null;

        if (!$session_id || !$payment_url) {
            return response()->json(['error' => 'Session de paiement invalide.'], 500);
        }

        // Enregistrer la transaction
        $transaction = Transaction::create([
            'tirelire_id' => $tirelire->id,
            'session_id' => $session_id,
            'montant' => $montant,
            'statut' => 'pending',
        ]);

        return response()->json([
            'message' => 'Session de paiement créée.',
            'payment_url' => $payment_url,
            'commission' => $commission,
            'net' => $montantNet,
            'transaction' => $transaction
        ]);
    }



    public function paymentSuccess(Request $request ,Tirelire $tirelire)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
        ]);

        $session_id = $validated['session_id'];

        // Vérifier le statut de paiement via Wave
        $response = Http::withHeaders([
            "Authorization" => "Bearer " . env('WAVE_API_KEY'),
            "Content-Type" => "application/json"
        ])->get("https://api.wave.com/v1/checkout/sessions/{$session_id}");

        if ($response->failed()) {
            return response()->json(['error' => 'Erreur lors de la vérification du statut de paiement.'], 500);
        }

        $payment_status = $response->json();
        $status = $payment_status['payment_status'] ?? 'unknown';
        $amount = $payment_status['amount'] ?? null;

        if ($status !== 'succeeded' || !$amount) {
            return response()->json(['error' => 'Le paiement a échoué ou le montant est invalide.'], 422);
        }

        // Trouver la transaction
        $transaction = Transaction::where('session_id', $session_id)->first();

        if (!$transaction) {
            return response()->json(['error' => 'Transaction non trouvée.'], 404);
        }

        // Mettre à jour la transaction et ajouter le montant à la tirelire
        $transaction->statut = 'succeeded';
        $transaction->save();

        $tirelire = $transaction->tirelire;
        $tirelire->montant += $amount;
        $tirelire->save();

        return response()->json([
            'message' => 'Paiement confirmé. Montant ajouté à la tirelire.',
            'tirelire' => $tirelire,
        ]);
    }


    public function myTirelires()
{
    // Récupérer l'utilisateur connecté
    $user = auth()->user();

    // Récupérer les tirelires de cet utilisateur
    $tirelires = $user->tirelires()->get();

    // Vérifier si des tirelires existent
    if ($tirelires->isEmpty()) {
        return response()->json(['message' => 'Aucune tirelire trouvée.'], 404);
    }

    // Retourner les tirelires avec leurs informations
    return response()->json(['tirelires' => $tirelires], 200);
}

public function showTirelire($id)
{
    // Récupérer la tirelire avec ses transactions et les utilisateurs associés
    $tirelire = Tirelire::with('transactions.user')->find($id);

    if (!$tirelire) {
        return response()->json(['error' => 'Tirelire non trouvée.'], 404);
    }

    return response()->json([
        'id' => $tirelire->id,
        'titre' => $tirelire->titre,
        'montant_objectif' => $tirelire->montant_objectif,
        'objectif' => $tirelire->objectif,

        'montant' => $tirelire->montant,
        'objectif' => $tirelire->objectif,
        'date_debut' => $tirelire->date_debut,
        'date_fin' => $tirelire->date_fin,
        'transactions' => $tirelire->transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'montant' => $transaction->montant,
                'date_transaction' => $transaction->updated_at,
            ];
        }),
    ], 200);
}

    public function retrait(Request $request, Tirelire $tirelire)
    {
        $request->validate([
            'montant' => 'required|numeric|min:1',
            'wave_phone' => 'nullable|string',
        ]);

        $user = auth()->user();

        // ✅ Vérifier les autorisations
        if ($tirelire->user_id !== $user->id) {
            return response()->json(['error' => 'Vous n\'êtes pas autorisé à effectuer un retrait sur cette tirelire.'], 403);
        }

        if ($tirelire->date_fin && $tirelire->date_fin > now()) {
            return response()->json(['error' => 'La date de fin n\'est pas encore atteinte.'], 400);
        }

        if ($tirelire->statut === 'termine') {
            return response()->json(['error' => 'Le retrait a déjà été effectué pour cette tirelire.'], 400);
        }

        if ($tirelire->montant < $request->montant) {
            return response()->json(['error' => 'Montant insuffisant'], 422);
        }

        try {
            DB::beginTransaction();

            // ✅ Calculer la commission dynamique (type: retrait)
            $commission = TarifService::calculerCommission($request->montant, 'retrait');
            $netAmount = $request->montant - $commission;

            if ($netAmount <= 0) {
                return response()->json(['error' => 'Le montant net après commission est invalide.'], 422);
            }

            $idempotencyKey = \Illuminate\Support\Str::uuid()->toString();

            // ✅ Appel API Wave
            $waveResponse = \Illuminate\Support\Facades\Http::withHeaders([
                "Authorization" => "Bearer " . env('WAVE_API_KEY'),
                "Content-Type" => "application/json",
                "Idempotency-Key" => $idempotencyKey,
            ])->post('https://api.wave.com/v1/payout', [
                        'receive_amount' => (string) $netAmount,
                        'currency' => 'XOF',
                        'mobile' => $request->wave_phone ?? $user->phone,
                        'description' => "Retrait tirelire ID: {$tirelire->id}",
                    ]);

            if (!$waveResponse->successful()) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Erreur API Wave',
                    'details' => $waveResponse->json()
                ], 500);
            }

            $waveData = $waveResponse->json();
            $session_id = $waveData['id'] ?? null;

            if (!$session_id) {
                DB::rollBack();
                return response()->json(['error' => 'Session ID introuvable dans la réponse Wave.'], 500);
            }

            // ✅ Enregistrer la transaction
            $tirelire->transactions()->create([
                'montant' => -$request->montant, // Retrait
                'user_id' => $user->id,
                'session_id' => $session_id,
                'commission' => $commission,
            ]);

            // ✅ Mettre à jour la tirelire
            $tirelire->montant -= $request->montant;
            if ($tirelire->montant <= 0) {
                $tirelire->statut = 'termine';
            }
            $tirelire->save();

            DB::commit();

            return response()->json([
                'message' => 'Retrait effectué avec succès via Wave.',
                'retrait_net' => $netAmount,
                'commission' => $commission,
                'wave' => $waveData,
                'tirelire' => $tirelire->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Erreur retrait tirelire ID {$tirelire->id}: " . $e->getMessage());
            return response()->json(['error' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }


    public function retirait(Request $request, Tirelire $tirelire)
{
    $request->validate([
        'montant'    => 'required|numeric|min:1',
        'wave_phone' => 'nullable|string',
    ]);

    $user = auth()->user();

    if ($tirelire->user_id !== $user->id) {
        return response()->json(['error' => 'Vous n\'êtes pas autorisé à effectuer un retrait sur cette tirelire.'], 403);
    }

    if ($tirelire->date_fin > now()) {
        return response()->json(['error' => 'La date de fin n\'est pas encore atteinte.'], 400);
    }

    if ($tirelire->statut === 'termine') {
        return response()->json(['error' => 'Le retrait a déjà été effectué pour cette tirelire.'], 400);
    }

    if ($tirelire->montant < $request->montant) {
        return response()->json(['message' => 'Montant insuffisant'], 422);
    }

    // 🔢 Appliquer la commission de 1.5%
    $commissionRate = 0.015;
    $commission = round($request->montant * $commissionRate, 0); // arrondi à l'entier
    $netAmount = $request->montant - $commission;

    try {
        DB::beginTransaction();

        $idempotencyKey = Str::uuid()->toString();

        $waveResponse = Http::withHeaders([
            "Authorization"   => "Bearer " . env('WAVE_API_KEY'),
            "Content-Type"    => "application/json",
            "Idempotency-Key" => $idempotencyKey,
        ])->post('https://api.wave.com/v1/payout', [
            'receive_amount' => (string) $netAmount,
            'currency'       => 'XOF',
            'mobile'         => $request->wave_phone ?? $user->phone,
        ]);

        if (!$waveResponse->successful()) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Erreur API Wave',
                'details' => $waveResponse->json()
            ], 500);
        }

        $waveData = $waveResponse->json();
        $session_id = $waveData['id'] ?? null;

        if (!$session_id) {
            DB::rollBack();
            return response()->json(['error' => 'Session ID introuvable dans la réponse Wave.'], 500);
        }

        $tirelire->transactions()->create([
            'montant'    => -$request->montant, // le montant demandé est retiré de la tirelire
            'user_id'    => $user->id,
            'session_id' => $session_id,
            'commission' => $commission,
        ]);

        $tirelire->montant -= $request->montant;
        $tirelire->save();

        DB::commit();

        return response()->json([
            'message'    => 'Retrait effectué avec succès via Wave',
            'retrait_net' => $netAmount,
            'commission' => $commission,
            'wave'       => $waveData,
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'Erreur : ' . $e->getMessage()], 500);
    }
}


public function retrait_any(Request $request, Tirelire $tirelire)
{
    $request->validate([
        'montant'    => 'required|numeric|min:1',
        'wave_phone' => 'nullable|string',
    ]);

    $user = auth()->user();

    if ($tirelire->user_id !== $user->id) {
        return response()->json(['error' => 'Vous n\'êtes pas autorisé à effectuer un retrait sur cette tirelire.'], 403);
    }



    if ($tirelire->montant < $request->montant) {
        return response()->json(['message' => 'Montant insuffisant'], 422);
    }

    // 🔢 Appliquer la commission de 1.5%
    $commissionRate = 0.05;
    $commission = round($request->montant * $commissionRate, 0); // arrondi à l'entier
    $netAmount = $request->montant - $commission;

    try {
        DB::beginTransaction();

        $idempotencyKey = \Illuminate\Support\Str::uuid()->toString();

        $waveResponse = \Illuminate\Support\Facades\Http::withHeaders([
            "Authorization"   => "Bearer " . env('WAVE_API_KEY'),
            "Content-Type"    => "application/json",
            "Idempotency-Key" => $idempotencyKey,
        ])->post('https://api.wave.com/v1/payout', [
            'receive_amount' => (string) $netAmount,
            'currency'       => 'XOF',
            'mobile'         => $request->wave_phone ?? $user->phone,
        ]);

        if (!$waveResponse->successful()) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Erreur API Wave',
                'details' => $waveResponse->json()
            ], 500);
        }

        $waveData = $waveResponse->json();
        $session_id = $waveData['id'] ?? null;

        if (!$session_id) {
            DB::rollBack();
            return response()->json(['error' => 'Session ID introuvable dans la réponse Wave.'], 500);
        }

        $tirelire->transactions()->create([
            'montant'    => -$request->montant, // le montant demandé est retiré de la tirelire
            'user_id'    => $user->id,
            'session_id' => $session_id,
            'commission' => $commission,
        ]);

        $tirelire->montant -= $request->montant;
        $tirelire->save();

        DB::commit();

        return response()->json([
            'message'    => 'Retrait effectué avec succès via Wave',
            'retrait_net' => $netAmount,
            'commission' => $commission,
            'wave'       => $waveData,
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'Erreur : ' . $e->getMessage()], 500);
    }
}





}
