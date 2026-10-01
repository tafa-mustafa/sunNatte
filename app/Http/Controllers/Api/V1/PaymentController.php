<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use App\Models\Tontine;
use App\Models\User;
use App\Services\MutuelleService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $payments,
        private MutuelleService $mutuelles,
    ) {
    }

    public function initiate(Request $request)
    {
        $request->validate([
            'tontine_id' => 'required|exists:tontines,id',
            'provider' => 'nullable|in:wave,orange_money',
        ]);

        $tontine = Tontine::findOrFail($request->tontine_id);
        $provider = $request->input('provider', Auth::user()->preferred_payment_method ?: 'wave');

        $result = $this->payments->initiate(Auth::user(), $tontine, $provider ?: 'wave');

        return response()->json($result['body'], $result['status']);
    }

    public function waveWebhook(Request $request)
    {
        $payload = $request->getContent();
        if (! $this->payments->verifyWaveSignature($payload, $request->header('Wave-Signature'))) {
            return response()->json(['error' => 'Signature Wave invalide.'], 401);
        }

        $event = json_decode($payload, true) ?: [];
        $data = $event['data'] ?? $event;
        $status = $data['payment_status'] ?? '';
        $type = $event['type'] ?? '';

        if ($status !== 'succeeded' && $type !== 'checkout.session.completed') {
            return response()->json(['ok' => true, 'ignored' => true]);
        }

        if (str_starts_with((string) ($data['client_reference'] ?? ''), 'mutuelle:')) {
            $mutuelle = $this->mutuelles->settleWave($data);
            if ($mutuelle) {
                return response()->json(['ok' => true, 'mutuelle_id' => $mutuelle->id]);
            }
        }

        try {
            $sessionId = $data['id'] ?? $data['checkout_session_id'] ?? null;
            $amount = (float) ($data['amount'] ?? 0);
            if (! $sessionId) {
                return response()->json(['error' => 'Session absente.'], 422);
            }

            $this->ensurePending($sessionId, $data, $amount);
            $contribution = $this->payments->settleSession($sessionId, $amount);

            return response()->json(['ok' => true, 'contribution_id' => $contribution->id]);
        } catch (RuntimeException $e) {
            $mutuelle = $this->mutuelles->settleWave($data);
            if ($mutuelle) {
                return response()->json(['ok' => true, 'mutuelle_id' => $mutuelle->id]);
            }

            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function receipt(Contribution $contribution)
    {
        $user = Auth::user();
        $isOwner = $contribution->tontine?->users()->wherePivot('badge', 'proprio')->where('users.id', $user->id)->exists();

        if ((int) $contribution->user_id !== (int) $user->id && ! $isOwner && (int) $user->role_id !== User::ROLE_ADMIN) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        if ($contribution->statut !== 'succeeded') {
            return response()->json(['message' => 'Le reçu est disponible après confirmation du paiement.'], 422);
        }

        if (! $contribution->recu_path || ! Storage::disk('public')->exists($contribution->recu_path)) {
            $contribution->recu_path = $this->payments->storeReceipt($contribution);
            $contribution->save();
        }

        return Storage::disk('public')->download($contribution->recu_path, 'recu-sununatt-'.$contribution->id.'.pdf');
    }

    private function ensurePending(string $sessionId, array $data, float $amount): void
    {
        if (Contribution::where('transaction_id', $sessionId)->exists()) {
            return;
        }

        $reference = $data['client_reference'] ?? '';
        if (! preg_match('/tontine:(\d+):user:(\d+)/', $reference, $matches)) {
            return;
        }

        Contribution::create([
            'transaction_id' => $sessionId,
            'tontine_id' => $matches[1],
            'user_id' => $matches[2],
            'montant' => $amount ?: 0,
            'statut' => 'pending',
            'provider' => 'wave',
        ]);
    }
}
