<?php

namespace Tests\Unit;

use App\Models\Tirelire;
use App\Models\User;
use App\Services\TirelireWithdrawalService;
use Tests\TestCase;

class TirelireWithdrawalServiceTest extends TestCase
{
    private function service(): TirelireWithdrawalService
    {
        return new TirelireWithdrawalService;
    }

    public function test_early_withdrawal_applies_five_percent_penalty(): void
    {
        $tirelire = new Tirelire([
            'montant' => 10000,
            'date_fin' => now()->addMonth()->toDateString(),
            'user_id' => 1,
            'statut' => 'en_cours',
        ]);

        $quote = $this->service()->quote($tirelire, 10000);

        $this->assertTrue($quote['early']);
        $this->assertTrue($quote['requires_penalty_acceptance']);
        $this->assertEquals(0.05, $quote['rate']);
        $this->assertEquals(500, $quote['penalty']);
        $this->assertEquals(9500, $quote['net']);
        $this->assertEquals('penalite_retrait_anticipe', $quote['fee_type']);
    }

    public function test_mature_withdrawal_applies_maturity_fee(): void
    {
        $tirelire = new Tirelire([
            'montant' => 10000,
            'date_fin' => now()->subDay()->toDateString(),
            'user_id' => 1,
            'statut' => 'en_cours',
        ]);

        $quote = $this->service()->quote($tirelire, 10000);

        $this->assertFalse($quote['early']);
        $this->assertFalse($quote['requires_penalty_acceptance']);
        $this->assertEquals(0.015, $quote['rate']);
        $this->assertEquals(0, $quote['penalty']);
        $this->assertEquals(150, $quote['fee']);
        $this->assertEquals(9850, $quote['net']);
        $this->assertEquals('frais_echeance', $quote['fee_type']);
    }

    public function test_guard_rejects_wrong_owner_and_empty_balance(): void
    {
        $owner = new User;
        $owner->id = 1;
        $other = new User;
        $other->id = 2;

        $tirelire = new Tirelire([
            'montant' => 500,
            'date_fin' => now()->addMonth()->toDateString(),
            'user_id' => 1,
            'statut' => 'en_cours',
        ]);

        $this->assertSame(403, $this->service()->guard($tirelire, $other, 100)['status']);
        $this->assertSame(422, $this->service()->guard($tirelire, $owner, 501)['status']);
        $this->assertNull($this->service()->guard($tirelire, $owner, 500));
    }
}
