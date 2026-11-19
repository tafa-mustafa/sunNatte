<?php

namespace Tests\Unit;

use App\Services\SmsGateway;
use App\Services\WavePayoutService;
use Tests\TestCase;

class WavePayoutServiceTest extends TestCase
{
    public function test_senegal_numbers_get_country_code(): void
    {
        $payouts = new WavePayoutService;

        $this->assertSame('+221771234567', $payouts->formatMobile('77 123 45 67'));
        $this->assertSame('+221771234567', $payouts->formatMobile('+221771234567'));
        $this->assertSame('+221771234567', $payouts->formatMobile('221771234567'));
    }

    public function test_payout_without_api_key_fails_gracefully(): void
    {
        config(['payments.wave.key' => null]);

        $result = (new WavePayoutService)->send('771234567', 1000);

        $this->assertFalse($result['ok']);
        $this->assertNull($result['id']);
    }

    public function test_sms_without_twilio_config_is_skipped(): void
    {
        config([
            'services.twilio.sid' => null,
            'services.twilio.auth_token' => null,
            'services.twilio.from' => null,
        ]);

        $this->assertFalse((new SmsGateway)->send('771234567', 'test'));
    }
}
