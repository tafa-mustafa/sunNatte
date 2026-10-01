<?php

namespace Tests\Unit;

use App\Models\Mutuelle;
use App\Models\MutuelleBeneficiaire;
use App\Services\MutuelleService;
use InvalidArgumentException;
use Tests\TestCase;

class MutuelleServiceTest extends TestCase
{
    private function service(): MutuelleService
    {
        return new MutuelleService;
    }

    public function test_zero_interest_keeps_principal(): void
    {
        $this->assertSame(50000.0, $this->service()->dueAmount(50000, 0));
    }

    public function test_interest_is_calculated_server_side(): void
    {
        $this->assertSame(52500.0, $this->service()->dueAmount(50000, 5));
    }

    public function test_csv_skips_header_and_parses_rows(): void
    {
        $csv = "nom;telephone;montant\nAwa Diop;771234567;50000\nMoussa Ndiaye;781112233;25000\n";
        $rows = $this->service()->parseCsv($csv);

        $this->assertCount(2, $rows);
        $this->assertSame('Awa Diop', $rows[0]['nom']);
        $this->assertSame('771234567', $rows[0]['phone']);
        $this->assertSame(50000.0, $rows[0]['montant']);
    }

    public function test_repayment_cannot_exceed_balance(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $beneficiaire = new MutuelleBeneficiaire([
            'montant_du' => 10000,
            'montant_rembourse' => 8000,
        ]);

        $this->service()->applyRepayment($beneficiaire, 3000);
    }

    public function test_mutuelle_closes_when_everyone_has_repaid(): void
    {
        $mutuelle = new Mutuelle;
        $mutuelle->setRelation('beneficiaires', collect([
            new MutuelleBeneficiaire(['montant_du' => 100, 'montant_rembourse' => 100]),
            new MutuelleBeneficiaire(['montant_du' => 50, 'montant_rembourse' => 50]),
        ]));

        $this->assertTrue($this->service()->canClose($mutuelle));
    }
}
