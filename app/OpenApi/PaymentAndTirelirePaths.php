<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/payments/initiate',
    tags: ['Paiements'],
    summary: 'Initier une cotisation Wave',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['tontine_id'],
        properties: [
            new OA\Property(property: 'tontine_id', type: 'integer'),
            new OA\Property(property: 'provider', type: 'string', enum: ['wave', 'orange_money'], example: 'wave'),
        ]
    )),
    responses: [
        new OA\Response(response: 200, description: 'URL Wave'),
        new OA\Response(response: 501, description: 'OM_NOT_AVAILABLE'),
    ]
)]
#[OA\Post(
    path: '/webhooks/wave',
    tags: ['Paiements'],
    summary: 'Webhook Wave (HMAC Wave-Signature)',
    parameters: [
        new OA\Parameter(name: 'Wave-Signature', in: 'header', required: true, schema: new OA\Schema(type: 'string')),
    ],
    requestBody: new OA\RequestBody(content: new OA\JsonContent(type: 'object')),
    responses: [
        new OA\Response(response: 200, description: 'Traité'),
        new OA\Response(response: 401, description: 'Signature invalide'),
    ]
)]
#[OA\Get(
    path: '/receipts/{contribution}',
    tags: ['Paiements'],
    summary: 'Télécharger le reçu PDF',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'contribution', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [
        new OA\Response(response: 200, description: 'application/pdf'),
        new OA\Response(response: 403, description: 'Accès refusé'),
    ]
)]
#[OA\Get(
    path: '/tontines/{tontine}/mycontributes',
    tags: ['Paiements'],
    summary: 'Mes cotisations sur une tontine',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tontine', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Liste')]
)]
#[OA\Post(
    path: '/tirelire/create',
    tags: ['Tirelire'],
    summary: 'Créer une tirelire + session Wave',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(content: new OA\JsonContent(
        required: ['titre', 'montant', 'montant_objectif', 'date_fin'],
        properties: [
            new OA\Property(property: 'titre', type: 'string'),
            new OA\Property(property: 'montant', type: 'number'),
            new OA\Property(property: 'montant_objectif', type: 'number'),
            new OA\Property(property: 'objectif', type: 'string'),
            new OA\Property(property: 'date_fin', type: 'string', format: 'date'),
        ]
    )),
    responses: [new OA\Response(response: 200, description: 'payment_url')]
)]
#[OA\Get(
    path: '/tirelires/my-tirelires',
    tags: ['Tirelire'],
    summary: 'Mes tirelires',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'Liste')]
)]
#[OA\Get(
    path: '/tirelires/{id}',
    tags: ['Tirelire'],
    summary: 'Détail tirelire',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Détail')]
)]
#[OA\Get(
    path: '/tirelires/{tirelire}/retrait/preview',
    tags: ['Tirelire'],
    summary: 'Simulation retrait (échéance vs pénalité 5 %)',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tirelire', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Quote')]
)]
#[OA\Post(
    path: '/tirelires/{tirelire}/retrait',
    tags: ['Tirelire'],
    summary: 'Retirer (accept_penalty si anticipé)',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tirelire', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    requestBody: new OA\RequestBody(content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'accept_penalty', type: 'boolean', example: true),
        ]
    )),
    responses: [
        new OA\Response(response: 200, description: 'Retrait'),
        new OA\Response(response: 422, description: 'Pénalité non acceptée'),
    ]
)]
#[OA\Post(
    path: '/tirelires/{tirelire}/retraits',
    tags: ['Tirelire'],
    summary: 'Alias retrait',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tirelire', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Retrait')]
)]
#[OA\Post(
    path: '/tirelire/{tirelire}/add',
    tags: ['Tirelire'],
    summary: 'Alimenter la tirelire',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tirelire', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    requestBody: new OA\RequestBody(content: new OA\JsonContent(
        properties: [new OA\Property(property: 'montant', type: 'number')]
    )),
    responses: [new OA\Response(response: 200, description: 'Session Wave')]
)]
class PaymentAndTirelirePaths
{
}
