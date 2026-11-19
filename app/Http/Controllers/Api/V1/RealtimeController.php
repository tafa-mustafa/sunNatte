<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RealtimeController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'driver' => config('broadcasting.default'),
            'auth_endpoint' => url('/api/broadcasting/auth'),
            'pusher' => [
                'key' => config('broadcasting.connections.pusher.key'),
                'cluster' => config('broadcasting.connections.pusher.options.cluster'),
            ],
            'channels' => [
                'user' => 'private-user.'.$user->id,
                'tontines' => $user->tontines()->pluck('tontines.id')
                    ->map(fn ($id) => 'private-tontine.'.$id)
                    ->values(),
            ],
            'events' => ['adhesion.updated', 'contribution.settled'],
        ]);
    }
}
