<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class SmsGateway
{
    public function send(string $to, string $message): bool
    {
        $to = trim($to);
        if ($to === '') {
            return false;
        }

        $driver = config('services.sms.driver', 'twilio');

        return match ($driver) {
            'africastalking' => $this->viaAfricasTalking($to, $message),
            'log' => $this->viaLog($to, $message),
            default => $this->viaTwilio($to, $message),
        };
    }

    private function viaTwilio(string $to, string $message): bool
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.auth_token');
        $from = config('services.twilio.from');
        if (! $sid || ! $token || ! $from) {
            return false;
        }

        try {
            (new Client($sid, $token))->messages->create($to, [
                'from' => $from,
                'body' => $message,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('SMS Twilio : '.$e->getMessage());

            return false;
        }
    }

    private function viaAfricasTalking(string $to, string $message): bool
    {
        $username = config('services.africastalking.username');
        $key = config('services.africastalking.api_key');
        $from = config('services.africastalking.from');
        if (! $username || ! $key) {
            return false;
        }

        try {
            $payload = [
                'username' => $username,
                'to' => $to,
                'message' => $message,
            ];
            if ($from) {
                $payload['from'] = $from;
            }

            $response = Http::withHeaders([
                'apiKey' => $key,
                'Accept' => 'application/json',
            ])->asForm()->post('https://api.africastalking.com/version1/messaging', $payload);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('SMS Africa\'s Talking : '.$e->getMessage());

            return false;
        }
    }

    private function viaLog(string $to, string $message): bool
    {
        Log::info('SMS ['.$to.'] '.$message);

        return true;
    }
}
