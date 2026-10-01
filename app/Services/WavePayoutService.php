<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WavePayoutService
{
    public function formatMobile(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($digits, '221')) {
            return '+'.$digits;
        }
        if (strlen($digits) === 9) {
            return '+221'.$digits;
        }

        return $digits !== '' ? '+'.$digits : '';
    }

    public function send(string $phone, float $amount, ?string $idempotency = null): array
    {
        $mobile = $this->formatMobile($phone);
        $key = config('payments.wave.key');
        if (! $key || $mobile === '' || $amount <= 0) {
            return ['ok' => false, 'id' => null, 'error' => 'Payout Wave impossible (clé, téléphone ou montant).'];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$key,
            'Content-Type' => 'application/json',
            'Idempotency-Key' => $idempotency ?: (string) Str::uuid(),
        ])->post(config('payments.wave.payout'), [
            'receive_amount' => (string) (int) round($amount),
            'currency' => config('payments.currency', 'XOF'),
            'mobile' => $mobile,
        ]);

        if (! $response->successful()) {
            return ['ok' => false, 'id' => null, 'error' => $response->json() ?? $response->body()];
        }

        return ['ok' => true, 'id' => $response['id'] ?? null, 'error' => null];
    }
}
