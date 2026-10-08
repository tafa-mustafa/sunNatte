<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/payment/success',
    tags: ['Paiements'],
    summary: 'Retour Wave succès (callback navigateur)',
    responses: [new OA\Response(response: 200, description: 'OK')]
)]
#[OA\Get(
    path: '/payment/error',
    tags: ['Paiements'],
    summary: 'Retour Wave erreur (callback navigateur)',
    responses: [new OA\Response(response: 200, description: 'Erreur paiement')]
)]
#[OA\Post(
    path: '/tontines/{tontine}/contribute',
    tags: ['Paiements'],
    summary: 'Cotiser (checkout Wave historique)',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tontine', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'wave_launch_url')]
)]
#[OA\Post(
    path: '/contribution/initiate/{tontine}',
    tags: ['Paiements'],
    summary: 'Initier une cotisation (alias PaymentService)',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tontine', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    requestBody: new OA\RequestBody(content: new OA\JsonContent(
        properties: [new OA\Property(property: 'provider', type: 'string', enum: ['wave', 'orange_money'])]
    )),
    responses: [
        new OA\Response(response: 200, description: 'Session Wave'),
        new OA\Response(response: 501, description: 'OM_NOT_AVAILABLE'),
    ]
)]
#[OA\Post(
    path: '/contribution/confirm/{tontine}',
    tags: ['Paiements'],
    summary: 'Confirmer une session Wave (polling)',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tontine', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['session_id'],
        properties: [new OA\Property(property: 'session_id', type: 'string')]
    )),
    responses: [
        new OA\Response(response: 200, description: 'Confirmé'),
        new OA\Response(response: 400, description: 'Paiement non succeeded'),
    ]
)]
#[OA\Post(
    path: '/wave/token',
    tags: ['Paiements'],
    summary: 'Créer une session Wave (montant libre)',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'amount', type: 'number', example: 1000),
            new OA\Property(property: 'currency', type: 'string', example: 'XOF'),
        ]
    )),
    responses: [new OA\Response(response: 200, description: 'Session Wave')]
)]
#[OA\Get(
    path: '/contribution/{id}',
    tags: ['Paiements'],
    summary: 'Détail d une cotisation',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Détail')]
)]
#[OA\Post(
    path: '/tirelire/payment/confirmed/{tirelire}',
    tags: ['Tirelire'],
    summary: 'Confirmer le paiement d alimentation',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tirelire', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Alimentée')]
)]
class ExtraPaymentPaths
{
}
