<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OpenApiController extends Controller
{
    public function __invoke(): BinaryFileResponse
    {
        foreach ([
            public_path('openapi.json'),
            storage_path('api-docs/api-docs.json'),
        ] as $path) {
            if (is_file($path)) {
                return response()->file($path, [
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache',
                ]);
            }
        }

        abort(404, 'Documentation OpenAPI introuvable. Exécutez php artisan l5-swagger:generate');
    }
}
