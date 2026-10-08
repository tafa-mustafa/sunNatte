<?php

namespace Tests\Unit;

use App\Models\Tontine;
use App\Services\TontineInviteService;
use Tests\TestCase;

class TontineInviteServiceTest extends TestCase
{
    private function service(): TontineInviteService
    {
        return new TontineInviteService;
    }

    public function test_generated_codes_are_six_readable_characters(): void
    {
        $alphabet = config('tontine.code_alphabet');

        for ($i = 0; $i < 40; $i++) {
            $code = $this->service()->generate();
            $this->assertSame(6, strlen($code));
            $this->assertSame(1, preg_match('/^['.$alphabet.']{6}$/', $code));
            $this->assertStringNotContainsString('0', $code);
            $this->assertStringNotContainsString('O', $code);
            $this->assertStringNotContainsString('1', $code);
            $this->assertStringNotContainsString('I', $code);
        }
    }

    public function test_normalize_strips_and_uppercases_the_code(): void
    {
        $this->assertSame('K7P2QM', $this->service()->normalize(' k7p-2qm '));
        $this->assertSame('', $this->service()->normalize('***'));
    }

    public function test_share_payload_includes_code_link_and_whatsapp_text(): void
    {
        $tontine = new Tontine([
            'nom' => 'GIE Liberté 6',
            'code_adhesion' => 'K7P2QM',
        ]);

        $share = $this->service()->share($tontine);

        $this->assertSame('K7P2QM', $share['code']);
        $this->assertStringContainsString('K7P2QM', $share['url']);
        $this->assertStringContainsString('K7P2QM', $share['text']);
        $this->assertStringContainsString('GIE Liberté 6', $share['text']);
        $this->assertStringStartsWith('https://wa.me/?text=', $share['whatsapp']);
        $this->assertStringContainsString('intent://join/K7P2QM', $share['intent']);
        $this->assertStringContainsString('com.teknon.sununatt', $share['intent']);
    }

    public function test_simulation_uses_monthly_share_and_duration(): void
    {
        $tontine = new Tontine([
            'montant' => 120000,
            'nombre_personne' => 12,
            'duree' => 12,
        ]);

        $simulation = $this->service()->simulation($tontine);

        $this->assertSame(10000, $simulation['versement']);
        $this->assertSame(12, $simulation['tours']);
        $this->assertEquals(120000, $simulation['percu']);
        $this->assertStringContainsString('10000', $simulation['resume']);
    }
}
