<?php

namespace App\Services;

use Illuminate\Http\Request;

class AdminAuthService
{
    public function credentials(Request $request): array
    {
        $login = trim((string) ($request->input('login')
            ?? $request->input('email')
            ?? $request->input('phone')
            ?? ''));

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        return [
            $field => $login,
            'password' => (string) $request->input('password'),
        ];
    }
}
