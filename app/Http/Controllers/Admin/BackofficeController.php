<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\V1\AdhesionController;
use App\Http\Controllers\Api\V1\Admin\AdminController;
use App\Http\Controllers\Controller;
use App\Models\Adhesion;
use App\Services\AdminDashboardService;
use Illuminate\Http\Request;

class BackofficeController extends Controller
{
    use CallsAdminApi;

    public function dashboard(AdminDashboardService $dashboard)
    {
        $stats = $this->fromApi(app(AdminController::class)->dashboard($dashboard));

        $cards = [
            ['label' => 'Utilisateurs', 'value' => $stats['users'] ?? 0, 'url' => route('admin.users')],
            ['label' => 'Utilisateurs actifs', 'value' => $stats['users_actifs'] ?? 0, 'url' => route('admin.users')],
            ['label' => 'Tontines', 'value' => $stats['tontines'] ?? 0, 'url' => route('admin.tontines')],
            ['label' => 'Catalogue', 'value' => $stats['tontines_catalogue'] ?? 0, 'url' => route('admin.tontines')],
            ['label' => 'Privées', 'value' => $stats['tontines_privees'] ?? 0, 'url' => route('admin.tontines')],
            ['label' => 'Adhésions en attente', 'value' => $stats['adhesions_pending'] ?? 0, 'url' => route('admin.adhesions')],
            ['label' => 'Mutuelles', 'value' => $stats['mutuelles'] ?? 0, 'url' => route('admin.mutuelles')],
            ['label' => 'Cotisations du jour (FCFA)', 'value' => number_format((float) ($stats['cotisations_du_jour'] ?? 0), 0, ',', ' '), 'url' => route('admin.tontines')],
        ];

        return view('admin.dashboard', compact('cards'));
    }

    public function adhesions()
    {
        $payload = $this->fromApi(app(AdhesionController::class)->pending());

        return view('admin.adhesions', [
            'adhesions' => $payload['adhesions'] ?? [],
        ]);
    }

    public function review(Request $request, Adhesion $adhesion)
    {
        return $this->flashApi(
            app(AdhesionController::class)->review($request, $adhesion),
            'Demande traitée.'
        );
    }
}
