<?php

return [

    /*
    | Retrait à l'échéance : frais Wave (1,5 %).
    | Retrait anticipé : pénalité Sunu Natt (10 %), en plus de l'acceptation explicite.
    */
    'maturity_fee_rate' => env('TIRELIRE_MATURITY_FEE_RATE', 0.015),
    'early_penalty_rate' => env('TIRELIRE_EARLY_PENALTY_RATE', 0.10),

];
