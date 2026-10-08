<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BackofficeAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && (int) Auth::user()->role_id === User::ROLE_ADMIN) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request, AdminAuthService $auth)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($auth->credentials($request), $request->boolean('remember'))) {
            return back()->withErrors(['login' => 'Identifiants invalides.'])->onlyInput('login');
        }

        $request->session()->regenerate();

        if ((int) Auth::user()->role_id !== User::ROLE_ADMIN) {
            Auth::logout();

            return back()->withErrors(['login' => 'Accès réservé aux administrateurs.']);
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
