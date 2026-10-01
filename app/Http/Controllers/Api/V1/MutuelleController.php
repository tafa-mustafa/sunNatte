<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Mutuelle;
use App\Models\MutuelleBeneficiaire;
use App\Services\MutuelleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class MutuelleController extends Controller
{
    public function __construct(private MutuelleService $mutuelles)
    {
    }

    public function index()
    {
        $user = Auth::user();
        $created = Mutuelle::with('beneficiaires')->where('createur_id', $user->id)->latest()->get();
        $received = Mutuelle::with('beneficiaires')
            ->whereHas('beneficiaires', fn ($q) => $q->where('user_id', $user->id))
            ->latest()
            ->get();

        return response()->json([
            'created' => $created->map(fn (Mutuelle $m) => $this->mutuelles->preview($m)),
            'received' => $received->map(fn (Mutuelle $m) => $this->mutuelles->preview($m)),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'objectif' => 'nullable|string|max:255',
            'taux_interet' => 'nullable|numeric|min:0|max:100',
            'duree_mois' => 'nullable|integer|min:1|max:60',
            'beneficiaires' => 'nullable|array',
        ]);

        $mutuelle = Mutuelle::create([
            'createur_id' => Auth::id(),
            'nom' => $request->nom,
            'objectif' => $request->objectif,
            'taux_interet' => $request->input('taux_interet', 0),
            'duree_mois' => $request->input('duree_mois', 1),
            'statut' => 'draft',
        ]);

        if ($request->filled('beneficiaires')) {
            try {
                $this->mutuelles->syncBeneficiaries($mutuelle, $request->beneficiaires);
            } catch (InvalidArgumentException $e) {
                $mutuelle->delete();

                return response()->json(['message' => $e->getMessage()], 422);
            }
        }

        return response()->json([
            'message' => 'Mutuelle créée. Vérifiez l\'aperçu puis alimentez.',
            'preview' => $this->mutuelles->preview($mutuelle->fresh('beneficiaires')),
        ], 201);
    }

    public function show(Mutuelle $mutuelle)
    {
        $this->assertParticipant($mutuelle);

        return response()->json($this->mutuelles->preview($mutuelle->load('beneficiaires')));
    }

    public function preview(Mutuelle $mutuelle)
    {
        return $this->show($mutuelle);
    }

    public function syncBeneficiaires(Request $request, Mutuelle $mutuelle)
    {
        $this->assertCreator($mutuelle);
        $request->validate(['beneficiaires' => 'required|array|min:1']);

        try {
            $this->mutuelles->syncBeneficiaries($mutuelle, $request->beneficiaires);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($this->mutuelles->preview($mutuelle->fresh('beneficiaires')));
    }

    public function import(Request $request, Mutuelle $mutuelle)
    {
        $this->assertCreator($mutuelle);
        $request->validate(['fichier' => 'required|file|mimes:csv,txt,text/plain']);

        try {
            $rows = $this->mutuelles->parseCsv($request->file('fichier')->get());
            $this->mutuelles->syncBeneficiaries($mutuelle, $rows);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($this->mutuelles->preview($mutuelle->fresh('beneficiaires')));
    }

    public function template()
    {
        $csv = "nom;telephone;montant\nAwa Diop;771234567;50000\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="beneficiaires-mutuelle.csv"',
        ]);
    }

    public function fund(Request $request, Mutuelle $mutuelle)
    {
        $this->assertCreator($mutuelle);
        $provider = $request->input('provider', 'wave');

        try {
            $result = $this->mutuelles->initiateFund($mutuelle, Auth::user(), $provider);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($result['body'], $result['status']);
    }

    public function repay(Request $request, Mutuelle $mutuelle)
    {
        $request->validate([
            'beneficiaire_id' => 'nullable|exists:mutuelle_beneficiaires,id',
            'montant' => 'required|numeric|min:1',
            'provider' => 'nullable|in:wave,orange_money',
        ]);

        $beneficiaire = $this->resolveBeneficiaire($request, $mutuelle);
        if (! $beneficiaire) {
            return response()->json(['message' => 'Bénéficiaire introuvable pour ce compte.'], 404);
        }

        $montant = (float) $request->montant;
        if ($montant > $beneficiaire->restant()) {
            return response()->json([
                'message' => 'Le montant dépasse le solde à rembourser ('.$beneficiaire->restant().' FCFA).',
            ], 422);
        }

        $provider = $request->input('provider', 'wave');
        if ($provider === 'orange_money') {
            return response()->json(['code' => 'OM_NOT_AVAILABLE', 'message' => 'Orange Money sera disponible prochainement.'], 501);
        }

        $key = config('payments.wave.key');
        if (! $key) {
            return response()->json(['error' => 'Clé API Wave manquante.'], 500);
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$key,
            'Content-Type' => 'application/json',
        ])->post(config('payments.wave.checkout'), [
            'amount' => (string) $request->montant,
            'currency' => 'XOF',
            'error_url' => route('payment.error', [], true),
            'success_url' => route('payment.success', [], true),
            'client_reference' => 'mutuelle:'.$mutuelle->id.':repay:'.$beneficiaire->id,
        ]);

        if ($response->failed() || ! isset($response['wave_launch_url'])) {
            return response()->json(['error' => 'Erreur session Wave.', 'details' => $response->json()], 500);
        }

        return response()->json([
            'message' => 'Validez le remboursement dans Wave.',
            'wave_launch_url' => $response['wave_launch_url'],
            'session_id' => $response['id'] ?? null,
            'montant' => $montant,
            'restant' => $beneficiaire->restant(),
        ]);
    }

    private function resolveBeneficiaire(Request $request, Mutuelle $mutuelle): ?MutuelleBeneficiaire
    {
        $user = Auth::user();
        $query = $mutuelle->beneficiaires();
        if ($request->filled('beneficiaire_id')) {
            $query->where('id', $request->beneficiaire_id);
        }

        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhere('phone', $user->phone);
        })->first();
    }

    private function assertCreator(Mutuelle $mutuelle): void
    {
        if ((int) $mutuelle->createur_id !== (int) Auth::id()) {
            abort(403, 'Seul le créateur peut modifier cette mutuelle.');
        }
    }

    private function assertParticipant(Mutuelle $mutuelle): void
    {
        $user = Auth::user();
        if ((int) $mutuelle->createur_id === (int) $user->id) {
            return;
        }
        $isBen = $mutuelle->beneficiaires()->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhere('phone', $user->phone);
        })->exists();
        if (! $isBen) {
            abort(403, 'Accès refusé.');
        }
    }
}
