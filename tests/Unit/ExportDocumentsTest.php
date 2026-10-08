<?php

namespace Tests\Unit;

use App\Models\Tontine;
use App\Models\User;
use App\Services\AssembleeService;
use App\Services\LedgerService;
use App\Services\ReceiptPdf;
use App\Services\SpreadsheetXlsx;
use Tests\TestCase;

class ExportDocumentsTest extends TestCase
{
    public function test_xlsx_is_a_zip_with_sheet(): void
    {
        $binary = (new SpreadsheetXlsx)->build(
            ['nom', 'montant'],
            [['nom' => 'Awa Diop', 'montant' => 10000]],
            'Suivi'
        );

        $this->assertSame('PK', substr($binary, 0, 2));
        $this->assertStringContainsString('Awa Diop', $this->sheetXml($binary));
    }

    public function test_ledger_xlsx_contains_headers(): void
    {
        $binary = (new LedgerService)->xlsx([
            [
                'date' => '01/10/2026',
                'tontine' => 'GIE Liberte 6',
                'membre' => 'Awa Diop',
                'montant' => 10000,
                'statut' => 'succeeded',
                'provider' => 'wave',
            ],
        ]);

        $this->assertSame('PK', substr($binary, 0, 2));
        $this->assertStringContainsString('GIE Liberte 6', $this->sheetXml($binary));
    }

    public function test_assemblee_pdf_lists_members_and_totals(): void
    {
        $tontine = new Tontine([
            'nom' => 'GIE Liberte 6',
            'montant' => 120000,
            'nombre_personne' => 4,
            'date_demarrage' => '2026-01-01',
            'date_fin' => '2026-12-01',
        ]);
        $member = new User(['nom' => 'Diop', 'prenom' => 'Awa', 'phone' => '771234567']);
        $tontine->setRelation('users', collect([$member]));
        $tontine->setRelation('tirages', collect());
        $tontine->setRelation('contributions', collect());

        $pdf = (new AssembleeService)->pdf($tontine);

        $this->assertStringStartsWith('%PDF-1.4', $pdf);
        $this->assertStringContainsString('assemblee', $pdf);
        $this->assertStringContainsString('GIE Liberte 6', $pdf);
        $this->assertStringContainsString('Awa Diop', $pdf);
        $this->assertStringContainsString('Aucun tirage', $pdf);
    }

    public function test_receipt_pdf_paginates_long_lists(): void
    {
        $lines = array_map(fn ($i) => 'Ligne '.$i, range(1, 50));
        $pdf = (new ReceiptPdf)->renderLines($lines);

        $this->assertStringStartsWith('%PDF-1.4', $pdf);
        $this->assertStringContainsString('Ligne 1', $pdf);
        $this->assertStringContainsString('Ligne 50', $pdf);
        $this->assertGreaterThan(1, substr_count($pdf, '/Type /Page /Parent'));
    }

    private function sheetXml(string $binary): string
    {
        $path = tempnam(sys_get_temp_dir(), 'chk');
        file_put_contents($path, $binary);
        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($path) === true);
        $xml = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($path);

        return $xml;
    }
}
