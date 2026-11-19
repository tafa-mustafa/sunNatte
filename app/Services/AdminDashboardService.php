<?php

namespace App\Services;

use App\Models\Adhesion;
use App\Models\Contribution;
use App\Models\Mutuelle;
use App\Models\Tontine;
use App\Models\User;

class AdminDashboardService
{
    public function stats(): array
    {
        return [
            'users' => User::where('role_id', '!=', User::ROLE_ADMIN)->count(),
            'users_actifs' => User::where('role_id', '!=', User::ROLE_ADMIN)->where('statut', true)->count(),
            'tontines' => Tontine::count(),
            'tontines_catalogue' => Tontine::where('visibilite', Tontine::VISIBILITE_CATALOGUE)->count(),
            'tontines_privees' => Tontine::where('visibilite', Tontine::VISIBILITE_PRIVEE)->count(),
            'adhesions_pending' => Adhesion::where('statut', AdhesionService::PENDING)->count(),
            'mutuelles' => Mutuelle::count(),
            'cotisations_du_jour' => (float) Contribution::whereDate('created_at', now()->toDateString())
                ->where('statut', 'succeeded')
                ->sum('montant'),
        ];
    }
}
