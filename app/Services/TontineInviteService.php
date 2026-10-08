<?php

namespace App\Services;

use App\Models\Tontine;
use App\Models\User;

class TontineInviteService
{
    public function generate(): string
    {
        $alphabet = config('tontine.code_alphabet', 'ABCDEFGHJKMNPQRSTUVWXYZ23456789');
        $length = (int) config('tontine.code_length', 6);
        $max = strlen($alphabet) - 1;
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $alphabet[random_int(0, $max)];
        }

        return $code;
    }

    public function generateUnique(): string
    {
        do {
            $code = $this->generate();
        } while ($this->exists($code));

        return $code;
    }

    public function normalize(?string $code): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $code) ?? '');
    }

    public function exists(string $code): bool
    {
        $code = $this->normalize($code);

        return $code !== '' && Tontine::whereRaw('UPPER(code_adhesion) = ?', [$code])->exists();
    }

    public function findByCode(?string $code): ?Tontine
    {
        $code = $this->normalize($code);
        if ($code === '') {
            return null;
        }

        return Tontine::whereRaw('UPPER(code_adhesion) = ?', [$code])->first();
    }

    public function share(Tontine $tontine): array
    {
        $code = $tontine->code_adhesion;
        $url = rtrim((string) config('tontine.join_url'), '/').'/'.$code;
        $play = (string) config('tontine.play_store');
        $ios = config('tontine.app_store');

        $text = "Rejoins ma tontine {$tontine->nom} sur Sunu Natt. Télécharge l'app : {$play}";
        if ($ios) {
            $text .= ' '.$ios;
        }
        $text .= ". Code d'accès : {$code}";

        $package = (string) config('tontine.android_package');
        $intent = 'intent://join/'.$code.'#Intent;scheme=https;package='.$package.';S.browser_fallback_url='.rawurlencode($url).';end';

        return [
            'code' => $code,
            'url' => $url,
            'intent' => $intent,
            'text' => $text,
            'whatsapp' => 'https://wa.me/?text='.rawurlencode($text),
        ];
    }

    public function simulation(Tontine $tontine): array
    {
        $n = max(1, (int) $tontine->nombre_personne);
        $montant = (float) $tontine->montant;
        $duree = (int) $tontine->duree;
        $versement = (int) round($montant / $n);

        return [
            'versement' => $versement,
            'tours' => $duree,
            'percu' => $montant,
            'resume' => "Chaque membre versera {$versement} FCFA pendant {$duree} mois, chacun recevra {$montant} FCFA à son tour.",
        ];
    }

    public function preview(Tontine $tontine, AdhesionService $adhesions, ?User $user = null): array
    {
        $approved = $adhesions->approvedCount($tontine);
        $remaining = $adhesions->remainingPlaces($tontine, $approved);
        $canJoin = $remaining > 0;
        $reason = $remaining > 0 ? null : 'full';

        if ($user) {
            $existing = $adhesions->findExisting($tontine, $user);
            if ($existing?->statut === AdhesionService::APPROVED) {
                $canJoin = false;
                $reason = 'already_member';
            } elseif ($existing?->statut === AdhesionService::PENDING) {
                $canJoin = false;
                $reason = 'pending';
            }
        }

        $createur = $tontine->users()->wherePivot('badge', 'proprio')->first();

        return [
            'tontine' => [
                'id' => $tontine->id,
                'nom' => $tontine->nom,
                'type' => $tontine->type,
                'visibilite' => $tontine->visibilite,
                'montant' => $tontine->montant,
                'duree' => $tontine->duree,
                'places_restantes' => $remaining,
                'nombre_personne' => (int) $tontine->nombre_personne,
                'date_demarrage' => $tontine->date_demarrage,
                'description' => $tontine->description,
                'createur' => $createur ? [
                    'id' => $createur->id,
                    'nom' => $createur->nom,
                    'prenom' => $createur->prenom,
                ] : null,
            ],
            'can_join' => $canJoin,
            'reason' => $reason,
            'next' => 'POST /api/adhesions',
            'simulation' => $this->simulation($tontine),
            'share' => $this->share($tontine),
        ];
    }

    public function createdPayload(Tontine $tontine): array
    {
        return [
            'message' => 'Tontine créée avec succès!',
            'tontine' => [
                'id' => $tontine->id,
                'nom' => $tontine->nom,
                'type' => $tontine->type,
                'visibilite' => $tontine->visibilite,
                'code' => $tontine->code_adhesion,
                'montant' => $tontine->montant,
                'duree' => $tontine->duree,
                'nombre_personne' => $tontine->nombre_personne,
            ],
            'share' => $this->share($tontine),
            'simulation' => $this->simulation($tontine),
        ];
    }
}
