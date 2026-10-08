<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\V1\Admin\AdminController;
use App\Http\Controllers\Api\V1\Admin\TarifController;
use App\Http\Controllers\Controller;
use App\Models\Tarif;
use App\Models\Tontine;
use App\Models\User;
use Illuminate\Http\Request;

class BackofficeCatalogController extends Controller
{
    use CallsAdminApi;

    public function tontines()
    {
        $tontines = $this->fromApi(app(AdminController::class)->list_tontine());
        if (! is_array($tontines) || isset($tontines['message'])) {
            $tontines = [];
        }

        return view('admin.tontines', compact('tontines'));
    }

    public function showTontine(Tontine $tontine)
    {
        $detail = $this->fromApi(app(AdminController::class)->show_tontine($tontine));

        return view('admin.tontine-show', ['tontine' => $detail]);
    }

    public function storeTontine(Request $request)
    {
        return $this->flashApi(app(AdminController::class)->store_tontine($request), 'Tontine créée.');
    }

    public function updateTontine(Request $request, Tontine $tontine)
    {
        return $this->flashApi(app(AdminController::class)->update_tontine($request, $tontine), 'Tontine mise à jour.');
    }

    public function toggleTontine(Tontine $tontine)
    {
        $admin = app(AdminController::class);
        $response = $tontine->statut
            ? $admin->desactive_tontine($tontine)
            : $admin->active_tontine($tontine);

        return $this->flashApi($response, 'Statut modifié.');
    }

    public function adhere(Request $request, Tontine $tontine)
    {
        return $this->flashApi(app(AdminController::class)->adherer_user($request, $tontine), 'Adhésion validée.');
    }

    public function users()
    {
        $users = $this->fromApi(app(AdminController::class)->list_users());

        return view('admin.users', ['users' => is_array($users) ? $users : []]);
    }

    public function showUser(User $user)
    {
        $payload = $this->fromApi(app(AdminController::class)->show_user($user));

        return view('admin.user-show', [
            'user' => $payload['user'] ?? [],
            'completeness' => $payload['completeness'] ?? [],
            'documents' => $this->fromApi(app(AdminController::class)->list_documents($user)),
        ]);
    }

    public function updateUser(Request $request, User $user)
    {
        return $this->flashApi(app(AdminController::class)->update_user($request, $user), 'Profil mis à jour.');
    }

    public function toggleUser(User $user)
    {
        $admin = app(AdminController::class);
        $response = $user->statut
            ? $admin->desactive_user($user->id)
            : $admin->active_user($user->id);

        return $this->flashApi($response, 'Statut modifié.');
    }

    public function mutuelles()
    {
        $mutuelles = $this->fromApi(app(AdminController::class)->list_mutuelles());

        return view('admin.mutuelles', ['mutuelles' => is_array($mutuelles) ? $mutuelles : []]);
    }

    public function tarifs()
    {
        $tarifs = $this->fromApi(app(TarifController::class)->index());

        return view('admin.tarifs', ['tarifs' => is_array($tarifs) ? $tarifs : []]);
    }

    public function storeTarif(Request $request)
    {
        return $this->flashApi(app(TarifController::class)->store($request), 'Tarif ajouté.');
    }

    public function destroyTarif(Tarif $tarif)
    {
        return $this->flashApi(app(TarifController::class)->destroy($tarif), 'Tarif supprimé.');
    }
}
