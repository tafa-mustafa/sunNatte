<?php

namespace Tests\Unit;

use App\Http\Controllers\WellKnownController;
use App\Services\FCMService;
use Tests\TestCase;

class FCMServiceTest extends TestCase
{
    public function test_send_is_false_without_server_key_or_token(): void
    {
        config(['fcm.server_key' => null]);

        $this->assertFalse((new FCMService)->send(null, 'Sunu Natt', 'hello'));
        $this->assertFalse((new FCMService)->send('device-token', 'Sunu Natt', 'hello'));
    }

    public function test_assetlinks_expose_android_package(): void
    {
        $payload = app(WellKnownController::class)->android()->getData(true);

        $this->assertSame('android_app', $payload[0]['target']['namespace']);
        $this->assertSame('com.teknon.sununatt', $payload[0]['target']['package_name']);
    }
}
