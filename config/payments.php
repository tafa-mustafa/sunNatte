<?php

return [

    'currency' => 'XOF',

    'wave' => [
        'key' => env('WAVE_API_KEY'),
        'checkout' => 'https://api.wave.com/v1/checkout/sessions',
        'payout' => 'https://api.wave.com/v1/payout',
        'webhook_secret' => env('WAVE_WEBHOOK_SECRET'),
    ],

    'play_store' => env(
        'TONTINE_PLAY_STORE_URL',
        'https://play.google.com/store/apps/details?id=com.teknon.sununatt'
    ),

];
