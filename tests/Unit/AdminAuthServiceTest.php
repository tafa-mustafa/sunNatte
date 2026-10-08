<?php

namespace Tests\Unit;

use App\Services\AdminAuthService;
use Illuminate\Http\Request;
use Tests\TestCase;

class AdminAuthServiceTest extends TestCase
{
    public function test_email_login_uses_email_field(): void
    {
        $creds = (new AdminAuthService)->credentials(new Request([
            'email' => 'admin@sununatt.sn',
            'password' => 'secret',
        ]));

        $this->assertSame('admin@sununatt.sn', $creds['email']);
        $this->assertSame('secret', $creds['password']);
    }

    public function test_phone_or_login_uses_phone_field(): void
    {
        $creds = (new AdminAuthService)->credentials(new Request([
            'login' => '771234567',
            'password' => 'secret',
        ]));

        $this->assertSame('771234567', $creds['phone']);
        $this->assertArrayNotHasKey('email', $creds);
    }
}
