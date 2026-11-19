<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Calebasse;
use App\Models\CalebasseCotisation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CabebaseController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $calebasses = Calebasse::whereHas('membres', fn ($q) => $q->where('user_id', $user->id))
            ->with(['createur:id,nom,prenom'])
            ->latest()
            ->get()
            ->map(fn (Calebasse $c) => $this->payload($c, $user->id));

        return response()->json(['calebasses' => $calebasses]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'          => 'required|string|max:150',
            'description'  => 'nullable|string|max:500',
            'type'         => 'nullable|in:projet,fete,urgence,epargne',
            'objectif'     => 'nullable|numeric|min:1',
            'date_echeance' => 'nullable|date|after:today',
        ]);

        $user = Auth::user();

        $calebasse = DB::transaction(function () use ($validated, $user) {
            $c = Calebasse::create([
                ...$validated,
                'createur_id'    => $user->id,
                'statut'         => 'open',
                'code_invitation' => $this->generateCode(),
            ]);
            $c->membres()->attach($user->id, ['badge' => 'createur']);

            return $c;
        });

        return response()->json([
            'message'    => 'Calebasse créée.',
            'calebasse'  => $this->payload($calebasse->fresh('membres'), $user->id),
        ], 201);
    }

    public function show(Calebasse $calebasse)
    {
        $user = Auth::user();

        if (! $calebasse->membres()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $calebasse->load(['createur:id,nom,prenom', 'membres:id,nom,prenom', 'cotisations.user:id,nom,prenom']);

        return response()->json(['calebasse' => $this->payload($calebasse, $user->id)]);
    }

    public function join(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $calebasse = Calebasse::where('code_invitation', strtoupper($request->code))->first();

        if (! $calebasse) {
            return response()->json(['message' => 'Code invalide.'], 404);
        }
        if ($calebasse->statut !== 'open') {
            return response()->json(['message' => 'Cette calebasse est fermée.'], 422);
        }

        $user = Auth::user();

        if ($calebasse->membres()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Vous êtes déjà membre.'], 422);
        }

        $calebasse->membres()->attach($user->id, ['badge' => 'membre']);

        return response()->json([
            'message'   => 'Vous avez rejoint la calebasse.',
            'calebasse' => $this->payload($calebasse->fresh('membres'), $user->id),
        ]);
    }

    public function cotiser(Request $request, Calebasse $calebasse)
    {
        $request->validate(['montant' => 'required|numeric|min:1']);

        $user = Auth::user();

        if (! $calebasse->membres()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => "Vous n'êtes pas membre de cette calebasse."], 403);
        }
        if ($calebasse->statut !== 'open') {
            return response()->json(['message' => 'Cette calebasse est fermée.'], 422);
        }

        $key = config('payments.wave.key');
        if (! $key) {
            return response()->json(['error' => 'Clé API Wave manquante.'], 500);
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$key,
            'Content-Type'  => 'application/json',
        ])->post('https://api.wave.com/v1/checkout/sessions', [
            'amount'      => (string) (int) $request->montant,
            'currency'    => 'XOF',
            'error_url'   => route('payment.error', [], true),
            'success_url' => route('payment.success', [], true),
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Erreur lors de la création de la session de paiement.'], 500);
        }

        $session = $response->json();

        if (empty($session['wave_launch_url'])) {
            return response()->json(['error' => 'Réponse Wave invalide.'], 500);
        }

        CalebasseCotisation::firstOrCreate(
            ['session_id' => $session['id']],
            [
                'calebasse_id' => $calebasse->id,
                'user_id'      => $user->id,
                'montant'      => $request->montant,
                'statut'       => 'pending',
                'provider'     => 'wave',
            ]
        );

        return response()->json([
            'wave_launch_url' => $session['wave_launch_url'],
            'session_id'      => $session['id'],
            'montant'         => $request->montant,
        ]);
    }

    public function confirm(Request $request, Calebasse $calebasse)
    {
        $request->validate(['session_id' => 'required|string']);

        $user = Auth::user();

        $cotisation = CalebasseCotisation::where('session_id', $request->session_id)
            ->where('calebasse_id', $calebasse->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $cotisation) {
            return response()->json(['error' => 'Session introuvable.'], 404);
        }
        if ($cotisation->statut === 'succeeded') {
            return response()->json(['message' => 'Cotisation déjà confirmée.', 'cotisation_id' => $cotisation->id]);
        }

        $key = config('payments.wave.key');
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$key,
            'Content-Type'  => 'application/json',
        ])->get('https://api.wave.com/v1/checkout/sessions/'.$request->session_id);

        if ($response->failed()) {
            return response()->json(['error' => 'Erreur de vérification du paiement.'], 500);
        }

        $status = $response->json('payment_status') ?? 'unknown';

        if ($status !== 'succeeded') {
            return response()->json(['error' => 'Paiement '.$status.'. Réessayez ou patientez.'], 400);
        }

        $cotisation->update(['statut' => 'succeeded']);

        return response()->json([
            'message'        => 'Cotisation de '.$cotisation->montant.' FCFA confirmée.',
            'cotisation_id'  => $cotisation->id,
            'total_collecte' => $calebasse->totalCollecte(),
        ]);
    }

    public function close(Calebasse $calebasse)
    {
        $user = Auth::user();

        if (! $calebasse->isCreateur($user)) {
            return response()->json(['message' => 'Seul le créateur peut fermer la calebasse.'], 403);
        }
        if ($calebasse->statut !== 'open') {
            return response()->json(['message' => 'La calebasse est déjà fermée.'], 422);
        }

        $calebasse->update(['statut' => 'closed']);

        return response()->json([
            'message'   => 'Calebasse fermée.',
            'calebasse' => $this->payload($calebasse->fresh(), $user->id),
        ]);
    }

    private function payload(Calebasse $c, int $userId): array
    {
        $total = $c->totalCollecte();

        return [
            'id'             => $c->id,
            'nom'            => $c->nom,
            'description'    => $c->description,
            'type'           => $c->type,
            'objectif'       => $c->objectif,
            'progression'    => $c->objectif ? round(($total / $c->objectif) * 100, 1) : null,
            'total_collecte' => $total,
            'date_echeance'  => $c->date_echeance?->toDateString(),
            'statut'         => $c->statut,
            'code_invitation' => $c->code_invitation,
            'is_createur'    => (int) $c->createur_id === $userId,
            'membres_count'  => $c->relationLoaded('membres') ? $c->membres->count() : $c->membres()->count(),
            'createur'       => $c->createur ? [
                'id'     => $c->createur->id,
                'nom'    => $c->createur->nom,
                'prenom' => $c->createur->prenom,
            ] : null,
            'cotisations'    => $c->relationLoaded('cotisations') ? $c->cotisations->map(fn ($cot) => [
                'id'     => $cot->id,
                'montant' => $cot->montant,
                'statut' => $cot->statut,
                'user'   => $cot->user ? $cot->user->nom.' '.$cot->user->prenom : null,
                'date'   => $cot->created_at->toDateString(),
            ]) : [],
        ];
    }

    private function generateCode(): string
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (Calebasse::where('code_invitation', $code)->exists());

        return $code;
    }
}
