<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\LedgerService;
use App\Services\NotificationPreferenceService;
use App\Services\ReceiptPdf;
use Tests\TestCase;

class NotificationPreferenceServiceTest extends TestCase
{
    public function test_matrix_defaults_match_ux_channels(): void
    {
        $prefs = new NotificationPreferenceService;
        $user = new User;

        $this->assertTrue($prefs->allows($user, 'rappel_cotisation', 'sms'));
        $this->assertFalse($prefs->allows($user, 'rappel_cotisation', 'email'));
        $this->assertTrue($prefs->allows($user, 'cotisation_versee', 'email'));
        $this->assertTrue($prefs->allows($user, 'cotisation_versee', 'sms'));
        $this->assertFalse($prefs->allows($user, 'cotisation_recue', 'sms'));
        $this->assertTrue($prefs->allows($user, 'adhesion_validee', 'push'));
    }

    public function test_ledger_pdf_starts_with_header(): void
    {
        $pdf = (new LedgerService)->pdf([
            [
                'date' => '01/10/2026',
                'tontine' => 'GIE Liberte 6',
                'membre' => 'Awa Diop',
                'montant' => '10000',
                'statut' => 'succeeded',
            ],
        ]);

        $this->assertStringStartsWith('%PDF-1.4', $pdf);
        $this->assertStringContainsString('Tableau de suivi', $pdf);
        $this->assertStringContainsString('Awa Diop', $pdf);
        $this->assertStringContainsString('SUNU NATT', (new ReceiptPdf)->renderLines(['SUNU NATT']));
    }
}
