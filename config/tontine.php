<?php

return [

    'join_url' => env('TONTINE_JOIN_URL', 'https://sununatt.sn/join'),

    'play_store' => env(
        'TONTINE_PLAY_STORE_URL',
        'https://play.google.com/store/apps/details?id=com.teknon.sununatt'
    ),

    'app_store' => env('TONTINE_APP_STORE_URL'),

    'android_package' => env('TONTINE_ANDROID_PACKAGE', 'com.teknon.sununatt'),

    'android_sha256' => env('TONTINE_ANDROID_SHA256', ''),

    'ios_app_id' => env('TONTINE_IOS_APP_ID'),

    'code_length' => 6,

    'code_alphabet' => 'ABCDEFGHJKMNPQRSTUVWXYZ23456789',

];
