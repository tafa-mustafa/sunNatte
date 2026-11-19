<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\LedgerService;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    public function index(Request $request, LedgerService $ledger)
    {
        return response()->json([
            'rows' => $ledger->rowsFor($request->user()),
        ]);
    }

    public function export(Request $request, LedgerService $ledger)
    {
        $pdf = $ledger->pdf($ledger->rowsFor($request->user()));

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="suivi-sununatt.pdf"',
        ]);
    }
}
