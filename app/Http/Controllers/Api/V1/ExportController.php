<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Mutuelle;
use App\Models\Tontine;
use App\Models\User;
use App\Services\AssembleeService;
use App\Services\LedgerService;
use App\Services\MutuelleService;
use App\Services\SpreadsheetXlsx;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function __construct(
        private SpreadsheetXlsx $xlsx,
        private LedgerService $ledger,
        private AssembleeService $assemblee,
        private MutuelleService $mutuelles,
    ) {
    }

    public function ledgerXlsx(Request $request)
    {
        return $this->xlsx->download(
            $this->ledger->xlsx($this->ledger->rowsFor($request->user())),
            'suivi-sununatt.xlsx'
        );
    }

    public function tontineXlsx(Request $request, Tontine $tontine)
    {
        $this->assertTontineMember($request->user(), $tontine);

        return $this->xlsx->download(
            $this->xlsx->build(
                ['type', 'date', 'nom', 'detail', 'montant', 'statut'],
                $this->assemblee->excelRows($tontine),
                'Tontine'
            ),
            'tontine-'.$tontine->id.'.xlsx'
        );
    }

    public function tontineAssemblee(Request $request, Tontine $tontine)
    {
        $this->assertTontineMember($request->user(), $tontine);
        $pdf = $this->assemblee->pdf($tontine);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="assemblee-tontine-'.$tontine->id.'.pdf"',
        ]);
    }

    public function mutuelleXlsx(Request $request, Mutuelle $mutuelle)
    {
        $this->assertMutuelleParticipant($request->user(), $mutuelle);
        $preview = $this->mutuelles->preview($mutuelle->load('beneficiaires'));
        $rows = [];
        foreach ($preview['beneficiaires'] as $b) {
            $rows[] = [
                'nom' => $b['nom'],
                'telephone' => $b['phone'],
                'alloue' => $b['montant_alloue'],
                'du' => $b['montant_du'],
                'rembourse' => $b['montant_rembourse'],
                'restant' => $b['restant'],
                'statut' => $b['statut'],
            ];
        }

        return $this->xlsx->download(
            $this->xlsx->build(
                ['nom', 'telephone', 'alloue', 'du', 'rembourse', 'restant', 'statut'],
                $rows,
                'Mutuelle'
            ),
            'mutuelle-'.$mutuelle->id.'.xlsx'
        );
    }

    public function mutuelleTemplate()
    {
        return $this->xlsx->download(
            $this->xlsx->build(
                ['nom', 'telephone', 'montant'],
                [['nom' => 'Awa Diop', 'telephone' => '771234567', 'montant' => '50000']],
                'Beneficiaires'
            ),
            'beneficiaires-mutuelle.xlsx'
        );
    }

    private function assertTontineMember(User $user, Tontine $tontine): void
    {
        if ((int) $user->role_id === User::ROLE_ADMIN) {
            return;
        }
        if (! $user->tontines()->where('tontines.id', $tontine->id)->exists()) {
            abort(403, 'Accès refusé.');
        }
    }

    private function assertMutuelleParticipant(User $user, Mutuelle $mutuelle): void
    {
        if ((int) $user->role_id === User::ROLE_ADMIN || (int) $mutuelle->createur_id === (int) $user->id) {
            return;
        }
        $ok = $mutuelle->beneficiaires()->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhere('phone', $user->phone);
        })->exists();
        if (! $ok) {
            abort(403, 'Accès refusé.');
        }
    }
}
