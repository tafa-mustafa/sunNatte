<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/mutuelles/template',
    tags: ['Mutuelle'],
    summary: 'Modèle CSV bénéficiaires',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'CSV')]
)]
#[OA\Get(
    path: '/mutuelles',
    tags: ['Mutuelle'],
    summary: 'Mutuelles créées / reçues',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'created + received')]
)]
#[OA\Post(
    path: '/mutuelles',
    tags: ['Mutuelle'],
    summary: 'Créer une mutuelle',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(content: new OA\JsonContent(
        required: ['nom'],
        properties: [
            new OA\Property(property: 'nom', type: 'string'),
            new OA\Property(property: 'objectif', type: 'string'),
            new OA\Property(property: 'taux_interet', type: 'number', example: 5),
        ]
    )),
    responses: [new OA\Response(response: 201, description: 'Créée')]
)]
#[OA\Get(
    path: '/mutuelles/{mutuelle}',
    tags: ['Mutuelle'],
    summary: 'Détail',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'mutuelle', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Détail')]
)]
#[OA\Get(
    path: '/mutuelles/{mutuelle}/preview',
    tags: ['Mutuelle'],
    summary: 'Aperçu intérêts / dû',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'mutuelle', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Preview')]
)]
#[OA\Post(
    path: '/mutuelles/{mutuelle}/beneficiaires',
    tags: ['Mutuelle'],
    summary: 'Synchroniser les bénéficiaires',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'mutuelle', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'OK')]
)]
#[OA\Post(
    path: '/mutuelles/{mutuelle}/import',
    tags: ['Mutuelle'],
    summary: 'Importer un CSV',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'mutuelle', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Importé')]
)]
#[OA\Post(
    path: '/mutuelles/{mutuelle}/fund',
    tags: ['Mutuelle'],
    summary: 'Alimenter (checkout Wave)',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'mutuelle', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [
        new OA\Response(response: 200, description: 'URL Wave'),
        new OA\Response(response: 501, description: 'OM_NOT_AVAILABLE'),
    ]
)]
#[OA\Post(
    path: '/mutuelles/{mutuelle}/repay',
    tags: ['Mutuelle'],
    summary: 'Rembourser',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'mutuelle', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Remboursement')]
)]
#[OA\Get(
    path: '/ledger',
    tags: ['Suivi'],
    summary: 'Tableau de suivi JSON',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'rows')]
)]
#[OA\Get(
    path: '/ledger/export.pdf',
    tags: ['Suivi'],
    summary: 'Export PDF du suivi',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'application/pdf')]
)]
#[OA\Get(
    path: '/ledger/export.xlsx',
    tags: ['Suivi'],
    summary: 'Export Excel du suivi',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'application/xlsx')]
)]
#[OA\Get(
    path: '/tontines/{tontine}/export.xlsx',
    tags: ['Tontines'],
    summary: 'Export Excel (membres, tirages, cotisations)',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tontine', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'application/xlsx')]
)]
#[OA\Get(
    path: '/tontines/{tontine}/assemblee.pdf',
    tags: ['Tontines'],
    summary: 'Procès-verbal d’assemblée (PDF)',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tontine', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'application/pdf')]
)]
#[OA\Get(
    path: '/mutuelles/template.xlsx',
    tags: ['Mutuelle'],
    summary: 'Modèle Excel bénéficiaires',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'application/xlsx')]
)]
#[OA\Get(
    path: '/mutuelles/{mutuelle}/export.xlsx',
    tags: ['Mutuelle'],
    summary: 'Export Excel de la mutuelle',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'mutuelle', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'application/xlsx')]
)]
#[OA\Get(
    path: '/notifications',
    tags: ['Suivi'],
    summary: 'Notifications in-app non lues',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'Liste')]
)]
#[OA\Get(
    path: '/notifications/preferences',
    tags: ['Suivi'],
    summary: 'Préférences push / SMS / e-mail',
    security: [['sanctum' => []]],
    responses: [
        new OA\Response(response: 200, description: 'Matrice', content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'events',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/NotificationPref')
                ),
            ]
        )),
    ]
)]
#[OA\Put(
    path: '/notifications/preferences',
    tags: ['Suivi'],
    summary: 'Mettre à jour un type d’événement',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['event'],
        properties: [
            new OA\Property(property: 'event', type: 'string', example: 'rappel_cotisation'),
            new OA\Property(property: 'push', type: 'boolean'),
            new OA\Property(property: 'sms', type: 'boolean'),
            new OA\Property(property: 'email', type: 'boolean'),
        ]
    )),
    responses: [new OA\Response(response: 200, description: 'Enregistré')]
)]
#[OA\Get(
    path: '/notifications/mark-as-read',
    tags: ['Suivi'],
    summary: 'Tout marquer lu',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'OK')]
)]
#[OA\Post(
    path: '/notifications/read/{id}',
    tags: ['Suivi'],
    summary: 'Marquer une notif lue',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
    responses: [new OA\Response(response: 200, description: 'OK')]
)]
#[OA\Delete(
    path: '/notifications/{id}',
    tags: ['Suivi'],
    summary: 'Supprimer une notification',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
    responses: [new OA\Response(response: 200, description: 'Supprimée')]
)]
#[OA\Post(
    path: '/admin/tontines/create',
    tags: ['Admin'],
    summary: 'Créer une tontine catalogue',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'nom', type: 'string'),
            new OA\Property(property: 'nombre_personne', type: 'integer'),
            new OA\Property(property: 'visibilite', type: 'string', enum: ['catalogue', 'privee']),
            new OA\Property(property: 'type', type: 'string'),
        ]
    )),
    responses: [new OA\Response(response: 201, description: 'Catalogue')]
)]
#[OA\Get(
    path: '/admin/tontines/all',
    tags: ['Admin'],
    summary: 'Lister les tontines',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'Liste')]
)]
class MutuelleAndOpsPaths
{
}
