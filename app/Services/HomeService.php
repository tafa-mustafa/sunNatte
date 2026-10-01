<?php

namespace App\Services;

use App\Models\User;

class HomeService
{
    public function __construct(private ProfileCompletenessService $profiles)
    {
    }

    public function welcome(User $user): string
    {
        $name = trim((string) ($user->prenom ?: $user->nom));

        return $name !== ''
            ? 'Bienvenue dans Sunu Natt, '.$name
            : 'Bienvenue dans Sunu Natt';
    }

    public function ctas(bool $kycReady): array
    {
        return [
            [
                'key' => 'create_tontine',
                'label' => 'Créer une tontine',
                'enabled' => $kycReady,
                'next' => $kycReady ? 'POST /api/tontine/create' : 'GET /api/profile/completeness',
            ],
            [
                'key' => 'join_tontine',
                'label' => 'Adhérer à une tontine',
                'enabled' => true,
                'next' => 'GET /api/tontines/filter',
            ],
            [
                'key' => 'join_by_code',
                'label' => 'Entrer un code d\'invitation',
                'enabled' => true,
                'next' => 'POST /api/tontines/join-by-code',
            ],
        ];
    }

    public function build(User $user): array
    {
        $completeness = $this->profiles->inspect($user, $user->documents()->count());

        return [
            'welcome' => $this->welcome($user),
            'user' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'avatar' => $user->avatar,
            ],
            'completeness' => $completeness,
            'notifications' => [
                'unread' => $user->unreadNotifications()->count(),
            ],
            'counters' => [
                'tontines' => $user->tontines()->count(),
                'tirelires' => $user->tirelires()->count(),
                'adhesions_pending' => $user->adhesions()->where('statut', 'pending')->count(),
                'mutuelles' => $user->mutuelles()->count(),
            ],
            'ctas' => $this->ctas($completeness['kyc_ready']),
            'nav' => ['accueil', 'tontines', 'tirelire', 'mutuel'],
        ];
    }
}
