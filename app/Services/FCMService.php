<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
class FCMService
{
    public static function sendNotification($token, $notification)
    {
        $response = Http::withToken(config('fcm.token'))
                        ->post('https://fcm.googleapis.com/fcm/send', [
                            'to' => $token,
                            'notification' => $notification,
                        ]);

        return $response->json();
    }
}