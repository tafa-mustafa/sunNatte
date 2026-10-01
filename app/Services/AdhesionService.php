<?php

namespace App\Services;

use App\Models\Adhesion;
use App\Models\Tontine;
use App\Models\User;
use InvalidArgumentException;

class AdhesionService
{
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    public const DELAY = '24-48h';
    public const DELAY_OWNER = 'Validation par le créateur';

    public function canSubmit(?Adhesion $existing): bool
    {
        if ($existing === null) {
            return true;
        }

        return $existing->statut === self::REJECTED;
    }

    public function remainingPlaces(Tontine $tontine, int $approvedCount): int
    {
        return max(0, (int) $tontine->nombre_personne - $approvedCount);
    }

    public function hasCapacity(Tontine $tontine, int $approvedCount): bool
    {
        return $this->remainingPlaces($tontine, $approvedCount) > 0;
    }

    public function canReview(User $user, Tontine $tontine, bool $isOwner): bool
    {
        if ((int) $user->role_id === User::ROLE_ADMIN) {
            return true;
        }

        return $isOwner && $tontine->isPrivee();
    }

    public function isOwner(Tontine $tontine, User $user): bool
    {
        return Adhesion::where('tontine_id', $tontine->id)
            ->where('user_id', $user->id)
            ->where('badge', 'proprio')
            ->where('statut', self::APPROVED)
            ->exists();
    }

    public function approve(Adhesion $adhesion, int $adminId): Adhesion
    {
        $adhesion->statut = self::APPROVED;
        if ($adhesion->badge !== 'proprio') {
            $adhesion->badge = 'membre';
        }
        $adhesion->motif_rejet = null;
        $adhesion->reviewed_at = now();
        $adhesion->reviewed_by = $adminId;

        return $adhesion;
    }

    public function reject(Adhesion $adhesion, int $adminId, ?string $motif): Adhesion
    {
        $motif = trim((string) $motif);
        if ($motif === '') {
            throw new InvalidArgumentException('Le motif de rejet est obligatoire.');
        }

        $adhesion->statut = self::REJECTED;
        $adhesion->motif_rejet = $motif;
        $adhesion->reviewed_at = now();
        $adhesion->reviewed_by = $adminId;

        return $adhesion;
    }

    public function reopen(Adhesion $adhesion): Adhesion
    {
        if ($adhesion->statut !== self::REJECTED) {
            throw new InvalidArgumentException('Seule une demande rejetée peut être complétée.');
        }

        $adhesion->statut = self::PENDING;
        $adhesion->motif_rejet = null;
        $adhesion->reviewed_at = null;
        $adhesion->reviewed_by = null;
        $adhesion->badge = 'demande';

        return $adhesion;
    }

    public function payload(Adhesion $adhesion): array
    {
        if ($adhesion->exists) {
            $adhesion->loadMissing(['tontine:id,nom,type,visibilite,montant,duree,date_demarrage', 'user:id,nom,prenom,phone']);
        }

        return [
            'id' => $adhesion->id,
            'statut' => $adhesion->statut,
            'badge' => $adhesion->badge,
            'motif_rejet' => $adhesion->motif_rejet,
            'delai_estime' => $adhesion->statut === self::PENDING
                ? ($adhesion->tontine?->isPrivee() ? self::DELAY_OWNER : self::DELAY)
                : null,
            'submitted_at' => $adhesion->created_at,
            'reviewed_at' => $adhesion->reviewed_at,
            'tontine' => $adhesion->tontine,
            'user' => $adhesion->user,
        ];
    }

    public function findExisting(Tontine $tontine, User $user): ?Adhesion
    {
        return Adhesion::where('tontine_id', $tontine->id)
            ->where('user_id', $user->id)
            ->first();
    }

    public function approvedCount(Tontine $tontine): int
    {
        return Adhesion::where('tontine_id', $tontine->id)
            ->where('statut', self::APPROVED)
            ->count();
    }
}
