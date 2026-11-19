<?php

namespace App\Http\Controllers;

use App\Services\TontineInviteService;

class PageController extends Controller
{
    public function home()
    {
        return view('home');
    }

    public function aPropos()
    {
        return view('a-propos');
    }

    public function contact()
    {
        return view('contact');
    }

    public function mentionsLegales()
    {
        return view('mentions-legales');
    }

    public function join(string $code, TontineInviteService $invites)
    {
        $tontine = $invites->findByCode($code);

        if (! $tontine) {
            abort(404);
        }

        return view('join', [
            'tontine' => $tontine,
            'code' => $tontine->code_adhesion,
            'share' => $invites->share($tontine),
        ]);
    }
}
