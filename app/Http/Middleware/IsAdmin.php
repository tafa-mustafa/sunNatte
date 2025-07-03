<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = auth()->user();

        if (!$user || $user->role_id != 1) {
            return response()->json([
                'message' => 'Accès refusé : vous n\'êtes pas administrateur.'
            ], 403);
        }

        return $next($request);

    }
}
