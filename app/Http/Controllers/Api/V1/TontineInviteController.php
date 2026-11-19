<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tontine;
use App\Services\AdhesionService;
use App\Services\TontineInviteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TontineInviteController extends Controller
{
    public function __construct(
        private TontineInviteService $invites,
        private AdhesionService $adhesions,
    ) {
    }

    public function joinByCode(Request $request)
    {
        $request->validate([
            'code' => 'required_without:code_adhesion|string',
            'code_adhesion' => 'required_without:code|string',
        ]);

        $tontine = $this->invites->findByCode($request->input('code', $request->input('code_adhesion')));

        if (! $tontine) {
            return response()->json(['message' => 'Aucune tontine correspondant à ce code d\'invitation.'], 404);
        }

        return response()->json($this->invites->preview($tontine, $this->adhesions, Auth::user()));
    }

    public function share(Tontine $tontine)
    {
        $user = Auth::user();
        $isMember = $tontine->users()->where('users.id', $user->id)->exists();

        if (! $isMember && (int) $user->role_id !== 1) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        return response()->json([
            'share' => $this->invites->share($tontine),
            'simulation' => $this->invites->simulation($tontine),
        ]);
    }
}
