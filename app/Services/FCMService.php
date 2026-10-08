<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FCMService
{
    public function send(?string $token, string $title, string $body): bool
    {
        $key = config('fcm.server_key');
        if (! $key || ! $token) {
            return false;
        }

        try {
            $response = Http::withToken($key)
                ->acceptJson()
                ->post('https://fcm.googleapis.com/fcm/send', [
                    'to' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('FCM non envoyé : '.$e->getMessage());

            return false;
        }
    }
}
