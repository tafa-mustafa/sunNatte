<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class WellKnownController extends Controller
{
    public function android(): JsonResponse
    {
        $fingerprints = array_values(array_filter(array_map('trim', explode(',', (string) config('tontine.android_sha256')))));

        return response()->json([[
            'relation' => ['delegate_permission/common.handle_all_urls'],
            'target' => [
                'namespace' => 'android_app',
                'package_name' => config('tontine.android_package'),
                'sha256_cert_fingerprints' => $fingerprints,
            ],
        ]])->header('Content-Type', 'application/json');
    }

    public function apple(): JsonResponse
    {
        $appId = config('tontine.ios_app_id');
        $apps = $appId ? [$appId] : [];

        return response()->json([
            'applinks' => [
                'apps' => [],
                'details' => $apps === [] ? [] : [[
                    'appID' => $appId,
                    'paths' => ['/join/*'],
                ]],
            ],
        ])->header('Content-Type', 'application/json');
    }
}
