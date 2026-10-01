<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class SmsGateway
{
    public function send(string $to, string $message): bool
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.auth_token');
        $from = config('services.twilio.from');
        if (! $sid || ! $token || ! $from || trim($to) === '') {
            return false;
        }

        try {
            (new Client($sid, $token))->messages->create($to, [
                'from' => $from,
                'body' => $message,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('SMS non envoyé : '.$e->getMessage());

            return false;
        }
    }
}
