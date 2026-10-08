<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;

trait CallsAdminApi
{
    protected function fromApi(JsonResponse $response, ?string $fallback = null): array|object
    {
        $payload = $response->getData(true);
        if ($response->getStatusCode() >= 400) {
            abort($response->getStatusCode(), $payload['message'] ?? $fallback ?? 'Erreur API admin.');
        }

        return $payload;
    }

    protected function flashApi(JsonResponse $response, string $ok)
    {
        $payload = $response->getData(true);
        $message = $payload['message'] ?? $ok;

        if ($response->getStatusCode() >= 400) {
            return back()->withErrors(['api' => $message])->withInput();
        }

        return back()->with('ok', $message);
    }
}
