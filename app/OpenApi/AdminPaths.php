<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/admin/dashboard',
    tags: ['Admin'],
    summary: 'Statistiques back-office',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'Compteurs')]
)]
#[OA\Get(
    path: '/admin/mutuelles',
    tags: ['Admin'],
    summary: 'Lister les mutuelles',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'Liste')]
)]
#[OA\Get(
    path: '/admin/test',
    tags: ['Admin'],
    summary: 'Ping admin',
    responses: [new OA\Response(response: 200, description: 'OK')]
)]
#[OA\Get(
    path: '/admin/tarifs',
    tags: ['Admin'],
    summary: 'Lister les tarifs',
    responses: [new OA\Response(response: 200, description: 'Liste')]
)]
#[OA\Post(
    path: '/admin/tarifs',
    tags: ['Admin'],
    summary: 'Créer un tarif',
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['type', 'pourcentage'],
        properties: [
            new OA\Property(property: 'type', type: 'string', enum: ['cotisation', 'retrait']),
            new OA\Property(property: 'min', type: 'number', nullable: true),
            new OA\Property(property: 'max', type: 'number', nullable: true),
            new OA\Property(property: 'pourcentage', type: 'number'),
        ]
    )),
    responses: [new OA\Response(response: 201, description: 'Créé')]
)]
#[OA\Get(
    path: '/admin/tarifs/{tarif}',
    tags: ['Admin'],
    summary: 'Voir un tarif',
    parameters: [new OA\Parameter(name: 'tarif', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Tarif')]
)]
#[OA\Put(
    path: '/admin/tarifs/{tarif}',
    tags: ['Admin'],
    summary: 'Modifier un tarif',
    parameters: [new OA\Parameter(name: 'tarif', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    requestBody: new OA\RequestBody(content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'min', type: 'number'),
            new OA\Property(property: 'max', type: 'number'),
            new OA\Property(property: 'pourcentage', type: 'number'),
        ]
    )),
    responses: [new OA\Response(response: 200, description: 'Mis à jour')]
)]
#[OA\Delete(
    path: '/admin/tarifs/{tarif}',
    tags: ['Admin'],
    summary: 'Supprimer un tarif',
    parameters: [new OA\Parameter(name: 'tarif', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Supprimé')]
)]
#[OA\Get(
    path: '/admin/tontines/show/{tontine}',
    tags: ['Admin'],
    summary: 'Détail tontine (admin)',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tontine', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Détail')]
)]
#[OA\Get(
    path: '/admin/tontines/open/{tontine}',
    tags: ['Admin'],
    summary: 'Ouvrir / activer une tontine',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tontine', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Activée')]
)]
#[OA\Get(
    path: '/admin/tontines/close/{tontine}',
    tags: ['Admin'],
    summary: 'Fermer une tontine',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tontine', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Fermée')]
)]
#[OA\Post(
    path: '/admin/tontines/update/{tontine}',
    tags: ['Admin'],
    summary: 'Modifier une tontine',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tontine', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Mise à jour')]
)]
#[OA\Get(
    path: '/admin/users/all',
    tags: ['Admin'],
    summary: 'Lister les utilisateurs (alias)',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'Liste')]
)]
#[OA\Get(
    path: '/admin/users',
    tags: ['Admin'],
    summary: 'Lister les utilisateurs',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'Liste')]
)]
#[OA\Post(
    path: '/admin/users',
    tags: ['Admin'],
    summary: 'Créer un utilisateur',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 201, description: 'Créé')]
)]
#[OA\Get(
    path: '/admin/users/show/{user}',
    tags: ['Admin'],
    summary: 'Voir un utilisateur (alias)',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Utilisateur')]
)]
#[OA\Get(
    path: '/admin/users/{user}',
    tags: ['Admin'],
    summary: 'Voir un utilisateur',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Utilisateur')]
)]
#[OA\Get(
    path: '/admin/users/documents/{user}',
    tags: ['Admin'],
    summary: 'Documents KYC d un utilisateur',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Documents')]
)]
#[OA\Post(
    path: '/admin/users/update/{user}',
    tags: ['Admin'],
    summary: 'Modifier un utilisateur (alias POST)',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Mis à jour')]
)]
#[OA\Put(
    path: '/admin/users/{user}',
    tags: ['Admin'],
    summary: 'Modifier un utilisateur',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Mis à jour')]
)]
#[OA\Delete(
    path: '/admin/users/delete/{user}',
    tags: ['Admin'],
    summary: 'Supprimer un utilisateur (alias)',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Supprimé')]
)]
#[OA\Delete(
    path: '/admin/users/{user}',
    tags: ['Admin'],
    summary: 'Supprimer un utilisateur',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Supprimé')]
)]
#[OA\Get(
    path: '/admin/users/active/{id}',
    tags: ['Admin'],
    summary: 'Activer un utilisateur (alias GET)',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Activé')]
)]
#[OA\Get(
    path: '/admin/users/desactive/{id}',
    tags: ['Admin'],
    summary: 'Désactiver un utilisateur (alias GET)',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Désactivé')]
)]
#[OA\Patch(
    path: '/admin/users/{id}/activate',
    tags: ['Admin'],
    summary: 'Activer un utilisateur',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Activé')]
)]
#[OA\Patch(
    path: '/admin/users/{id}/deactivate',
    tags: ['Admin'],
    summary: 'Désactiver un utilisateur',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Désactivé')]
)]
#[OA\Post(
    path: '/admin/tontine/adhesion/{tontine}',
    tags: ['Admin'],
    summary: 'Déposer une adhésion catalogue (alias POST /adhesions)',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tontine', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 201, description: 'Demande pending')]
)]
#[OA\Post(
    path: '/admin/tontine/adhere_user/{tontine}',
    tags: ['Admin'],
    summary: 'Approuver l adhésion d un user_id',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'tontine', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['user_id'],
        properties: [new OA\Property(property: 'user_id', type: 'integer')]
    )),
    responses: [new OA\Response(response: 200, description: 'Approuvée')]
)]
class AdminPaths
{
}
