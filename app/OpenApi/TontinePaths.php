<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/tontine/create',
    tags: ['Tontines'],
    summary: 'Créer une tontine privée (proprio approved)',
    description: 'KYC requis. Génère un code 6 caractères + payload WhatsApp. visibilite=privee.',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['nom', 'nombre_personne', 'type', 'duree', 'montant', 'tirage'],
        properties: [
            new OA\Property(property: 'nom', type: 'string', example: 'GIE Liberté 6'),
            new OA\Property(property: 'nombre_personne', type: 'integer', example: 12),
            new OA\Property(property: 'type', type: 'string', example: 'argent'),
            new OA\Property(property: 'duree', type: 'integer', example: 12),
            new OA\Property(property: 'montant', type: 'number', example: 120000),
            new OA\Property(property: 'tirage', type: 'string'),
            new OA\Property(property: 'date_demarrage', type: 'string', format: 'date'),
            new OA\Property(property: 'description', type: 'string'),
        ]
    )),
    responses: [
        new OA\Response(response: 201, description: 'Créée + share'),
        new OA\Response(response: 422, description: 'PROFILE_INCOMPLETE ou dates'),
    ]
)]
#[OA\Get(
    path: '/tontine/list',
    tags: ['Tontines'],
    summary: 'Mes tontines (approved)',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'Liste')]
)]
#[OA\Get(
    path: '/tontines/filter',
    tags: ['Tontines'],
    summary: 'Catalogue public (visibilite=catalogue)',
    security: [['sanctum' => []]],
    parameters: [
        new OA\Parameter(name: 'type', in: 'query', schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'nom', in: 'query', schema: new OA\Schema(type: 'string')),
    ],
    responses: [new OA\Response(response: 200, description: 'Tontines catalogue')]
)]
#[OA\Get(
    path: '/tontine/{tontine}/detail',
    tags: ['Tontines'],
    summary: 'Détail d’une tontine',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tontine', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Détail')]
)]
#[OA\Post(
    path: '/tontines/join-by-code',
    tags: ['Tontines'],
    summary: 'Aperçu par code (pas d’adhésion)',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'code', type: 'string', example: 'K7P2QM'),
            new OA\Property(property: 'code_adhesion', type: 'string'),
        ]
    )),
    responses: [
        new OA\Response(response: 200, description: 'Preview + next POST /adhesions'),
        new OA\Response(response: 404, description: 'Code inconnu'),
    ]
)]
#[OA\Post(
    path: '/tontine/adhesion',
    tags: ['Tontines'],
    summary: 'Alias de join-by-code (preview)',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(content: new OA\JsonContent(
        properties: [new OA\Property(property: 'code_adhesion', type: 'string')]
    )),
    responses: [new OA\Response(response: 200, description: 'Preview')]
)]
#[OA\Get(
    path: '/tontines/{tontine}/share',
    tags: ['Tontines'],
    summary: 'Régénérer le message WhatsApp',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tontine', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [
        new OA\Response(response: 200, description: 'Share', content: new OA\JsonContent(
            properties: [new OA\Property(property: 'share', ref: '#/components/schemas/TontineShare')]
        )),
        new OA\Response(response: 403, description: 'Pas membre'),
    ]
)]
#[OA\Post(
    path: '/adhesions',
    tags: ['Adhésions'],
    summary: 'Déposer une demande (pending)',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(content: new OA\MediaType(
        mediaType: 'multipart/form-data',
        schema: new OA\Schema(properties: [
            new OA\Property(property: 'tontine_id', type: 'integer'),
            new OA\Property(property: 'invitation_code', type: 'string'),
            new OA\Property(property: 'documents[]', type: 'array', items: new OA\Items(type: 'string', format: 'binary')),
        ])
    )),
    responses: [
        new OA\Response(response: 201, description: 'Demande envoyée'),
        new OA\Response(response: 422, description: 'KYC, capacité, déjà membre'),
    ]
)]
#[OA\Get(
    path: '/adhesions/mine',
    tags: ['Adhésions'],
    summary: 'Mes demandes',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'Liste')]
)]
#[OA\Get(
    path: '/adhesions/pending',
    tags: ['Adhésions'],
    summary: 'File de validation',
    description: 'Proprio : tontines privées. Admin : tout.',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'Demandes pending')]
)]
#[OA\Post(
    path: '/adhesions/{adhesion}/review',
    tags: ['Adhésions'],
    summary: 'Approuver ou refuser',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'adhesion', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['action'],
        properties: [
            new OA\Property(property: 'action', type: 'string', enum: ['approve', 'reject']),
            new OA\Property(property: 'motif', type: 'string', description: 'Obligatoire si reject'),
        ]
    )),
    responses: [
        new OA\Response(response: 200, description: 'Traité'),
        new OA\Response(response: 403, description: 'Pas reviewer'),
        new OA\Response(response: 422, description: 'Motif manquant ou déjà traité'),
    ]
)]
#[OA\Patch(
    path: '/adhesions/{adhesion}',
    tags: ['Adhésions'],
    summary: 'Compléter une demande rejetée',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'adhesion', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Renvoyée')]
)]
#[OA\Get(
    path: '/admin/adhesions/pending',
    tags: ['Admin'],
    summary: 'Alias pending (admin voit tout)',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'Pending')]
)]
#[OA\Post(
    path: '/admin/adhesions/{adhesion}/review',
    tags: ['Admin'],
    summary: 'Alias review admin',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'adhesion', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Traité')]
)]
class TontinePaths
{
}
