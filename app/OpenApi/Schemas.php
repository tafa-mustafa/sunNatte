<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Message',
    properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]
)]
#[OA\Schema(
    schema: 'TokenUser',
    properties: [
        new OA\Property(property: 'token', type: 'string', example: '1|xxxxxxxx'),
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'nom', type: 'string'),
        new OA\Property(property: 'prenom', type: 'string'),
        new OA\Property(property: 'phone', type: 'string', example: '771234567'),
        new OA\Property(property: 'email', type: 'string', nullable: true),
        new OA\Property(property: 'statut', type: 'string', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'Completeness',
    properties: [
        new OA\Property(property: 'kyc_ready', type: 'boolean'),
        new OA\Property(property: 'score', type: 'integer'),
        new OA\Property(property: 'missing', type: 'array', items: new OA\Items(type: 'string')),
    ]
)]
#[OA\Schema(
    schema: 'TontineShare',
    properties: [
        new OA\Property(property: 'code', type: 'string', example: 'K7P2QM'),
        new OA\Property(property: 'url', type: 'string'),
        new OA\Property(property: 'text', type: 'string'),
        new OA\Property(property: 'whatsapp', type: 'string'),
    ]
)]
#[OA\Schema(
    schema: 'Adhesion',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'statut', type: 'string', enum: ['pending', 'approved', 'rejected']),
        new OA\Property(property: 'badge', type: 'string'),
        new OA\Property(property: 'motif_rejet', type: 'string', nullable: true),
        new OA\Property(property: 'delai_estime', type: 'string', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'NotificationPref',
    properties: [
        new OA\Property(property: 'event', type: 'string', example: 'cotisation_versee'),
        new OA\Property(property: 'push', type: 'boolean'),
        new OA\Property(property: 'sms', type: 'boolean'),
        new OA\Property(property: 'email', type: 'boolean'),
    ]
)]
class Schemas
{
}
