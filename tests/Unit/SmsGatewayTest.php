<?php

namespace Tests\Unit;

use App\Services\SmsGateway;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsGatewayTest extends TestCase
{
    public function test_twilio_is_false_without_credentials(): void
    {
        config([
            'services.sms.driver' => 'twilio',
            'services.twilio.sid' => null,
            'services.twilio.auth_token' => null,
            'services.twilio.from' => null,
        ]);

        $this->assertFalse((new SmsGateway)->send('+221700000000', 'Sunu Natt'));
        $this->assertFalse((new SmsGateway)->send('', 'Sunu Natt'));
    }

    public function test_africastalking_is_false_without_credentials(): void
    {
        config([
            'services.sms.driver' => 'africastalking',
            'services.africastalking.username' => null,
            'services.africastalking.api_key' => null,
        ]);

        $this->assertFalse((new SmsGateway)->send('+221700000000', 'Sunu Natt'));
    }

    public function test_africastalking_posts_messaging_api(): void
    {
        config([
            'services.sms.driver' => 'africastalking',
            'services.africastalking.username' => 'sandbox',
            'services.africastalking.api_key' => 'test-key',
            'services.africastalking.from' => 'SunuNatt',
        ]);

        Http::fake([
            'api.africastalking.com/*' => Http::response(['SMSMessageData' => ['Recipients' => []]], 201),
        ]);

        $this->assertTrue((new SmsGateway)->send('+221700000000', 'Bonjour'));

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'version1/messaging')
                && $request->hasHeader('apiKey', 'test-key');
        });
    }

    public function test_log_driver_always_succeeds(): void
    {
        config(['services.sms.driver' => 'log']);

        $this->assertTrue((new SmsGateway)->send('+221700000000', 'log'));
    }
}
