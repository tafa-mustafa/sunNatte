<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\HomeService;
use App\Services\ProfileCompletenessService;
use Tests\TestCase;

class HomeServiceTest extends TestCase
{
    private function home(): HomeService
    {
        return new HomeService(new ProfileCompletenessService);
    }

    public function test_welcome_uses_first_name(): void
    {
        $user = new User(['prenom' => 'Awa', 'nom' => 'Diop']);

        $this->assertSame('Bienvenue dans Sunu Natt, Awa', $this->home()->welcome($user));
    }

    public function test_create_cta_is_disabled_until_kyc_is_ready(): void
    {
        $disabled = collect($this->home()->ctas(false))->firstWhere('key', 'create_tontine');
        $enabled = collect($this->home()->ctas(true))->firstWhere('key', 'create_tontine');

        $this->assertFalse($disabled['enabled']);
        $this->assertSame('GET /api/profile/completeness', $disabled['next']);
        $this->assertTrue($enabled['enabled']);
        $this->assertSame('POST /api/tontine/create', $enabled['next']);
    }
}
