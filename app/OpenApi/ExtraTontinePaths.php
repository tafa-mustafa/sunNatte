<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/tontine/{tontine}/tirage',
    tags: ['Tontines'],
    summary: 'Tirages de la tontine',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tontine', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Tirages')]
)]
#[OA\Get(
    path: '/tontine/{tontine}/listeVersements',
    tags: ['Tontines'],
    summary: 'Liste des versements',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tontine', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Versements')]
)]
#[OA\Get(
    path: '/tontine/avenir',
    tags: ['Tontines'],
    summary: 'Tontines à venir (places restantes)',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'Liste')]
)]
#[OA\Get(
    path: '/tontine/expired',
    tags: ['Tontines'],
    summary: 'Tontines arrivées à échéance',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'Liste')]
)]
#[OA\Post(
    path: '/tontines/{tontine}/tirages',
    tags: ['Tontines'],
    summary: 'Programmer un tirage (proprio)',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tontine', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['user_id', 'date_versement'],
        properties: [
            new OA\Property(property: 'user_id', type: 'integer'),
            new OA\Property(property: 'date_versement', type: 'string', format: 'date'),
        ]
    )),
    responses: [
        new OA\Response(response: 201, description: 'Programmé'),
        new OA\Response(response: 403, description: 'Pas le créateur'),
    ]
)]
#[OA\Patch(
    path: '/tontines/{tontine}/tirages/{tirage}/complete',
    tags: ['Tontines'],
    summary: 'Marquer un tirage comme effectué',
    security: [['sanctum' => []]],
    parameters: [
        new OA\Parameter(name: 'tontine', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        new OA\Parameter(name: 'tirage', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    responses: [new OA\Response(response: 200, description: 'Complété')]
)]
#[OA\Post(
    path: '/tontines/{tontine}/add-participant',
    tags: ['Tontines'],
    summary: 'Ajouter un participant (proprio)',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tontine', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['user_id'],
        properties: [new OA\Property(property: 'user_id', type: 'integer')]
    )),
    responses: [new OA\Response(response: 200, description: 'Ajouté')]
)]
#[OA\Post(
    path: '/tontine/materiel/post',
    tags: ['Matériel'],
    summary: 'Créer un matériel de tontine',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'Créé')]
)]
#[OA\Get(
    path: '/tontine/materiel/get',
    tags: ['Matériel'],
    summary: 'Lister les matériels',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'Liste')]
)]
#[OA\Get(
    path: '/tontine/materiel/{materiel}/get',
    tags: ['Matériel'],
    summary: 'Détail d un matériel',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'materiel', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Détail')]
)]
class ExtraTontinePaths
{
}
