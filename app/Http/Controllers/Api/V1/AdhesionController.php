<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Adhesion;
use App\Models\Document;
use App\Models\Tontine;
use App\Models\User;
use App\Notifications\AdhesionNotification;
use App\Notifications\AdhesionRejectedNotification;
use App\Notifications\DemandeValidatedNotification;
use App\Services\AdhesionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class AdhesionController extends Controller
{
    public function __construct(private AdhesionService $adhesions)
    {
    }

    public function store(Request $request)
    {
        $request->validate([
            'tontine_id' => 'required_without:invitation_code|nullable|exists:tontines,id',
            'invitation_code' => 'required_without:tontine_id|nullable|string',
            'documents' => 'nullable|array',
            'documents.*' => 'file|max:5120',
        ]);

        $user = Auth::user();
        $invites = app(\App\Services\TontineInviteService::class);

        if ($request->filled('invitation_code')) {
            $fromCode = $invites->findByCode($request->invitation_code);
            if (! $fromCode) {
                return response()->json(['message' => 'Code d\'invitation invalide.'], 404);
            }
            if ($request->filled('tontine_id') && (int) $request->tontine_id !== (int) $fromCode->id) {
                return response()->json(['message' => 'Le code ne correspond pas à cette tontine.'], 422);
            }
            $tontine = $fromCode;
        } else {
            $tontine = Tontine::findOrFail($request->tontine_id);
        }

        $documentCount = $user->documents()->count();
        if ($request->hasFile('documents')) {
            $documentCount += count($request->file('documents'));
        }

        $incomplete = app(\App\Services\ProfileCompletenessService::class)
            ->incompleteResponse($user, $documentCount, 'adhesion');
        if ($incomplete) {
            return response()->json($incomplete, 422);
        }

        if (! $this->adhesions->hasCapacity($tontine, $this->adhesions->approvedCount($tontine))) {
            return response()->json(['message' => 'Le nombre maximum de participants est atteint.'], 422);
        }

        $existing = $this->adhesions->findExisting($tontine, $user);
        if ($existing && $existing->statut === AdhesionService::APPROVED) {
            return response()->json(['message' => 'Vous êtes déjà membre de cette tontine.'], 422);
        }
        if ($existing && $existing->statut === AdhesionService::PENDING) {
            return response()->json([
                'message' => 'Votre demande est déjà en attente de validation.',
                'adhesion' => $this->adhesions->payload($existing),
            ], 422);
        }

        if ($existing && $existing->statut === AdhesionService::REJECTED) {
            $adhesion = $this->adhesions->reopen($existing);
            $adhesion->save();
        } else {
            $adhesion = Adhesion::create([
                'tontine_id' => $tontine->id,
                'user_id' => $user->id,
                'badge' => 'demande',
                'statut' => AdhesionService::PENDING,
            ]);
        }

        $this->storeDocuments($request, $user, $adhesion);
        $this->notifyReviewers($tontine);

        return response()->json([
            'message' => 'Demande envoyée. Validation sous 24-48h.',
            'adhesion' => $this->adhesions->payload($adhesion->fresh()),
        ], 201);
    }

    public function mine()
    {
        $adhesions = Adhesion::with(['tontine:id,nom,type,montant,duree,date_demarrage', 'user:id,nom,prenom,phone'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(fn (Adhesion $adhesion) => $this->adhesions->payload($adhesion));

        return response()->json([
            'adhesions' => $adhesions,
        ]);
    }

    public function update(Request $request, Adhesion $adhesion)
    {
        if ((int) $adhesion->user_id !== (int) Auth::id()) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $request->validate([
            'documents' => 'nullable|array',
            'documents.*' => 'file|max:5120',
        ]);

        try {
            $this->adhesions->reopen($adhesion);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $adhesion->save();
        $this->storeDocuments($request, Auth::user(), $adhesion);
        $this->notifyReviewers($adhesion->tontine);

        return response()->json([
            'message' => 'Demande complétée et renvoyée pour validation.',
            'adhesion' => $this->adhesions->payload($adhesion->fresh()),
        ]);
    }

    public function pending()
    {
        $user = Auth::user();
        $query = Adhesion::with(['tontine:id,nom,type,visibilite,montant', 'user:id,nom,prenom,phone'])
            ->where('statut', AdhesionService::PENDING)
            ->latest();

        if ((int) $user->role_id !== User::ROLE_ADMIN) {
            $ownedIds = Adhesion::where('user_id', $user->id)
                ->where('badge', 'proprio')
                ->where('statut', AdhesionService::APPROVED)
                ->pluck('tontine_id');

            $query->whereIn('tontine_id', $ownedIds)
                ->whereHas('tontine', fn ($q) => $q->where('visibilite', Tontine::VISIBILITE_PRIVEE));
        }

        $adhesions = $query->get()->map(fn (Adhesion $adhesion) => $this->adhesions->payload($adhesion));

        return response()->json(['adhesions' => $adhesions]);
    }

    public function review(Request $request, Adhesion $adhesion)
    {
        $tontine = $adhesion->tontine;
        $user = Auth::user();
        $isOwner = $tontine ? $this->adhesions->isOwner($tontine, $user) : false;
        if (! $tontine || ! $this->adhesions->canReview($user, $tontine, $isOwner)) {
            abort(403, 'Accès refusé : vous ne pouvez pas valider cette demande.');
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'motif' => 'nullable|string|max:500',
        ]);

        if ($adhesion->statut !== AdhesionService::PENDING) {
            return response()->json(['message' => 'Cette demande a déjà été traitée.'], 422);
        }

        $tontine = $adhesion->tontine;

        try {
            if ($request->action === 'approve') {
                if (! $this->adhesions->hasCapacity($tontine, $this->adhesions->approvedCount($tontine))) {
                    return response()->json(['message' => 'Le nombre maximum de participants est atteint.'], 422);
                }
                $this->adhesions->approve($adhesion, Auth::id());
                $adhesion->save();
                $welcome = 'Bienvenue dans la tontine '.$tontine->nom;
                $validated = new DemandeValidatedNotification($tontine, [
                    'adhesion_id' => $adhesion->id,
                    'type' => 'adhesion_validee',
                    'message' => $welcome,
                ]);
                app(\App\Services\NotificationDispatcher::class)->send(
                    $adhesion->user,
                    'adhesion_validee',
                    $welcome,
                    $validated
                );
            } else {
                $this->adhesions->reject($adhesion, Auth::id(), $request->motif);
                $adhesion->save();
                $fresh = $adhesion->fresh('tontine');
                $rejected = new AdhesionRejectedNotification($fresh);
                app(\App\Services\NotificationDispatcher::class)->send(
                    $adhesion->user,
                    'adhesion_rejetee',
                    $rejected->toArray($adhesion->user)['message'],
                    $rejected
                );
            }
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => $request->action === 'approve' ? 'Demande approuvée.' : 'Demande rejetée.',
            'adhesion' => $this->adhesions->payload($adhesion->fresh()),
        ]);
    }

    private function notifyReviewers(Tontine $tontine): void
    {
        $dispatcher = app(\App\Services\NotificationDispatcher::class);

        if ($tontine->isPrivee()) {
            $createur = $tontine->users()->wherePivot('badge', 'proprio')->first()
                ?? $tontine->users()->first();
            if ($createur) {
                $notice = new AdhesionNotification($tontine, ['type' => 'nouveau_membre']);
                $dispatcher->send($createur, 'nouveau_membre', $notice->toArray($createur)['message'], $notice);
            }

            return;
        }

        User::where('role_id', User::ROLE_ADMIN)
            ->get()
            ->each(function (User $admin) use ($tontine, $dispatcher) {
                $notice = new AdhesionNotification($tontine, ['type' => 'nouveau_membre']);
                $dispatcher->send($admin, 'nouveau_membre', $notice->toArray($admin)['message'], $notice);
            });
    }

    private function storeDocuments(Request $request, User $user, Adhesion $adhesion): void
    {
        if (! $request->hasFile('documents')) {
            Document::where('user_id', $user->id)
                ->whereNull('adhesion_id')
                ->update(['adhesion_id' => $adhesion->id]);

            return;
        }

        foreach ($request->file('documents') as $file) {
            $fileName = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('documents'), $fileName);

            Document::create([
                'nom' => $file->getClientOriginalName(),
                'document' => 'documents/'.$fileName,
                'user_id' => $user->id,
                'adhesion_id' => $adhesion->id,
            ]);
        }
    }
}
