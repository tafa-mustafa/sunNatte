<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\NotificationPreferenceService;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function index(Request $request, NotificationPreferenceService $prefs)
    {
        return response()->json([
            'events' => $prefs->matrix($request->user()),
        ]);
    }

    public function update(Request $request, NotificationPreferenceService $prefs)
    {
        $request->validate([
            'event' => 'required|string',
            'push' => 'nullable|boolean',
            'sms' => 'nullable|boolean',
            'email' => 'nullable|boolean',
        ]);

        $result = $prefs->update($request->user(), $request->event, $request->only(['push', 'sms', 'email']));
        if (! $result['ok']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => 'Préférences enregistrées.',
            'events' => $prefs->matrix($request->user()),
        ]);
    }
}
