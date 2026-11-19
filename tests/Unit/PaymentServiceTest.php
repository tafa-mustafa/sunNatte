<?php

namespace Tests\Unit;

use App\Models\Tontine;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\ReceiptPdf;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    private function payments(): PaymentService
    {
        return new PaymentService(new ReceiptPdf);
    }

    public function test_amount_due_is_the_monthly_share(): void
    {
        $tontine = new Tontine(['montant' => 120000, 'nombre_personne' => 12]);

        $this->assertSame(10000.0, $this->payments()->amountDue($tontine));
    }

    public function test_orange_money_returns_not_available(): void
    {
        $result = $this->payments()->initiate(new User, new Tontine, 'orange_money');

        $this->assertSame(501, $result['status']);
        $this->assertSame('OM_NOT_AVAILABLE', $result['body']['code']);
    }

    public function test_wave_signature_matches_hmac(): void
    {
        $payload = '{"type":"checkout.session.completed"}';
        $secret = 'whsec_test';
        $timestamp = '1710000000';
        $hash = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
        $header = 't='.$timestamp.',v1='.$hash;

        $this->assertTrue($this->payments()->verifyWaveSignature($payload, $header, $secret));
        $this->assertFalse($this->payments()->verifyWaveSignature($payload, 't='.$timestamp.',v1=deadbeef', $secret));
    }

    public function test_receipt_pdf_starts_with_pdf_header(): void
    {
        $pdf = (new ReceiptPdf)->render([
            'reference' => 'SN-12',
            'date' => '01/10/2026',
            'membre' => 'Awa Diop',
            'tontine' => 'GIE Liberte 6',
            'montant' => '10000',
            'provider' => 'WAVE',
        ]);

        $this->assertStringStartsWith('%PDF-1.4', $pdf);
        $this->assertStringContainsString('SUNU NATT', $pdf);
        $this->assertStringContainsString('10000', $pdf);
    }
}
