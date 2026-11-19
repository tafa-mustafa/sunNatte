<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Sunu Natt API',
    version: '1.0.0',
    description: 'API Laravel Sanctum. Token via POST /users/login puis Authorize. Tontine privee = code + review proprio. Catalogue = filtre + review admin. Orange Money = 501. Page invitation hors API : GET /join/{code}.'
)]
#[OA\Server(url: '/api', description: 'Préfixe /api')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum',
    description: 'Coller le plainTextToken de login / register'
)]
#[OA\Tag(name: 'Auth', description: 'OTP, inscription, connexion, mot de passe')]
#[OA\Tag(name: 'Profil', description: 'Compte, KYC, accueil')]
#[OA\Tag(name: 'Tontines', description: 'Création, catalogue, invitations')]
#[OA\Tag(name: 'Adhésions', description: 'Demandes pending, review proprio/admin')]
#[OA\Tag(name: 'Paiements', description: 'Cotisations Wave, reçus, webhook')]
#[OA\Tag(name: 'Tirelire', description: 'Épargne verrouillée, retrait avec pénalité')]
#[OA\Tag(name: 'Mutuelle', description: 'Cagnotte, bénéficiaires, fonds, remboursement')]
#[OA\Tag(name: 'Suivi', description: 'Ledger PDF, notifications')]
#[OA\Tag(name: 'Matériel', description: 'Tontines matérielles et documents')]
#[OA\Tag(name: 'Admin', description: 'Catalogue, utilisateurs, tarifs')]
#[OA\Tag(name: 'Temps réel', description: 'Pusher / Echo, canaux privés')]
class OpenApiSpec
{
}
