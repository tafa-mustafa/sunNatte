<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class OpenApiController extends Controller
{
    public function __invoke(?string $file = null): Response
    {
        $name = $file ?: request('file', 'api-docs.json');
        if ($name === '' || $name === '1' || ! is_string($name)) {
            $name = 'api-docs.json';
        }
        if (! in_array($name, ['api-docs.json', 'api-docs.yaml'], true)) {
            abort(404);
        }

        $candidates = [
            public_path('openapi.json'),
            public_path($name),
            storage_path('api-docs/'.$name),
        ];

        foreach ($candidates as $path) {
            if (File::exists($path)) {
                $mime = str_ends_with($name, '.yaml') ? 'application/yaml' : 'application/json';

                return response(File::get($path), 200, [
                    'Content-Type' => $mime,
                    'Access-Control-Allow-Origin' => '*',
                    'Cache-Control' => 'no-cache',
                ]);
            }
        }

        abort(404, 'Documentation OpenAPI introuvable. Exécutez php artisan l5-swagger:generate');
    }
}
