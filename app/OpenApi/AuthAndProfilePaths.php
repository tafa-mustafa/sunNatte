<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/users/register',
    tags: ['Auth'],
    summary: 'Inscription',
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['nom', 'prenom', 'phone', 'password', 'password_confirmation'],
        properties: [
            new OA\Property(property: 'nom', type: 'string'),
            new OA\Property(property: 'prenom', type: 'string'),
            new OA\Property(property: 'phone', type: 'string', example: '771234567'),
            new OA\Property(property: 'email', type: 'string', nullable: true),
            new OA\Property(property: 'password', type: 'string'),
            new OA\Property(property: 'password_confirmation', type: 'string'),
        ]
    )),
    responses: [
        new OA\Response(response: 200, description: 'Inscrit'),
        new OA\Response(response: 400, description: 'Validation'),
    ]
)]
#[OA\Post(
    path: '/users/login',
    tags: ['Auth'],
    summary: 'Connexion téléphone / mot de passe',
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['phone', 'password'],
        properties: [
            new OA\Property(property: 'phone', type: 'string', example: '771234567'),
            new OA\Property(property: 'password', type: 'string'),
        ]
    )),
    responses: [
        new OA\Response(response: 200, description: 'Token Sanctum', content: new OA\JsonContent(ref: '#/components/schemas/TokenUser')),
        new OA\Response(response: 401, description: 'Identifiants invalides'),
    ]
)]
#[OA\Post(
    path: '/mobile/sendCode',
    tags: ['Auth'],
    summary: 'Envoyer un OTP SMS',
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['phone'],
        properties: [new OA\Property(property: 'phone', type: 'string')]
    )),
    responses: [new OA\Response(response: 200, description: 'Code envoyé')]
)]
#[OA\Post(
    path: '/mobile/verify',
    tags: ['Auth'],
    summary: 'Vérifier l’OTP',
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['phone', 'code'],
        properties: [
            new OA\Property(property: 'phone', type: 'string'),
            new OA\Property(property: 'code', type: 'string', example: '1234'),
        ]
    )),
    responses: [
        new OA\Response(response: 200, description: 'OK'),
        new OA\Response(response: 422, description: 'Code invalide'),
    ]
)]
#[OA\Post(
    path: '/users/send-otp',
    tags: ['Auth'],
    summary: 'OTP mot de passe oublié',
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['phone'],
        properties: [new OA\Property(property: 'phone', type: 'string')]
    )),
    responses: [new OA\Response(response: 200, description: 'Code envoyé')]
)]
#[OA\Post(
    path: '/users/reset-password',
    tags: ['Auth'],
    summary: 'Réinitialiser le mot de passe',
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['phone', 'code', 'password', 'password_confirmation'],
        properties: [
            new OA\Property(property: 'phone', type: 'string'),
            new OA\Property(property: 'code', type: 'string'),
            new OA\Property(property: 'password', type: 'string'),
            new OA\Property(property: 'password_confirmation', type: 'string'),
        ]
    )),
    responses: [new OA\Response(response: 200, description: 'Mot de passe mis à jour')]
)]
#[OA\Post(
    path: '/admin/login',
    tags: ['Admin'],
    summary: 'Connexion admin (e-mail ou téléphone, role_id=1)',
    requestBody: new OA\RequestBody(content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'login', type: 'string'),
            new OA\Property(property: 'email', type: 'string'),
            new OA\Property(property: 'phone', type: 'string'),
            new OA\Property(property: 'password', type: 'string'),
        ]
    )),
    responses: [new OA\Response(response: 200, description: 'Token admin')]
)]
#[OA\Post(
    path: '/users/logout',
    tags: ['Auth'],
    summary: 'Déconnexion',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'OK')]
)]
#[OA\Get(
    path: '/home',
    tags: ['Profil'],
    summary: 'Accueil (CTA, compteurs, KYC)',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'Dashboard')]
)]
#[OA\Get(
    path: '/realtime/config',
    tags: ['Temps réel'],
    summary: 'Config Echo / Pusher (driver, auth, canaux, events)',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'driver, auth_endpoint, pusher, channels, events')]
)]
#[OA\Post(
    path: '/broadcasting/auth',
    tags: ['Temps réel'],
    summary: 'Autoriser un canal privé (Sanctum)',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'Auth canal')]
)]
#[OA\Get(
    path: '/users/profile',
    tags: ['Profil'],
    summary: 'Profil courant',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'Profil')]
)]
#[OA\Get(
    path: '/profile/completeness',
    tags: ['Profil'],
    summary: 'Complétude KYC',
    security: [['sanctum' => []]],
    responses: [
        new OA\Response(response: 200, description: 'Score KYC', content: new OA\JsonContent(ref: '#/components/schemas/Completeness')),
    ]
)]
#[OA\Post(
    path: '/users/update/password',
    tags: ['Profil'],
    summary: 'Changer le mot de passe',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'OK')]
)]
#[OA\Post(
    path: '/users/update/profile/{user}',
    tags: ['Profil'],
    summary: 'Mettre à jour le profil (KYC tardif)',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Profil')]
)]
#[OA\Post(
    path: '/users/document',
    tags: ['Profil'],
    summary: 'Uploader un document d’identité',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'Document enregistré')]
)]
#[OA\Get(
    path: '/users/{user}',
    tags: ['Profil'],
    summary: 'Voir un utilisateur',
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Profil public')]
)]
#[OA\Post(
    path: '/users/add',
    tags: ['Profil'],
    summary: 'Ajouter un utilisateur',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'Créé')]
)]
#[OA\Post(
    path: '/users/fcm-token',
    tags: ['Profil'],
    summary: 'Enregistrer le token FCM (push)',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['fcm_token'],
        properties: [new OA\Property(property: 'fcm_token', type: 'string')]
    )),
    responses: [new OA\Response(response: 200, description: 'Enregistré')]
)]
class AuthAndProfilePaths
{
}
