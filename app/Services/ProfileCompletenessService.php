<?php

namespace App\Services;

use App\Models\User;

class ProfileCompletenessService
{
    public const FIELDS = [
        'nom' => 'Nom',
        'prenom' => 'Prénom',
        'phone' => 'Numéro de téléphone',
        'adresse' => 'Adresse',
        'profession' => 'Situation professionnelle',
        'identite' => 'Pièce d\'identité (CNI ou passeport)',
        'documents' => 'Justificatifs (pièces jointes)',
    ];

    public function inspect(User $user, int $documentCount = 0): array
    {
        $filled = [
            'nom' => $this->present($user->nom),
            'prenom' => $this->present($user->prenom),
            'phone' => $this->present($user->phone),
            'adresse' => $this->present($user->adresse),
            'profession' => $this->present($user->profession),
            'identite' => $this->present($user->num_cni) || $this->present($user->num_passport),
            'documents' => $documentCount > 0,
        ];

        $missing = [];
        foreach (self::FIELDS as $key => $label) {
            if (! $filled[$key]) {
                $missing[] = ['key' => $key, 'label' => $label];
            }
        }

        $total = count(self::FIELDS);
        $done = $total - count($missing);

        return [
            'kyc_ready' => $missing === [],
            'can_create_tontine' => $missing === [],
            'percent' => (int) round(($done / $total) * 100),
            'filled' => $filled,
            'missing' => $missing,
        ];
    }

    public function incompleteResponse(User $user, int $documentCount, string $action = 'create_tontine'): ?array
    {
        $completeness = $this->inspect($user, $documentCount);
        if ($completeness['kyc_ready']) {
            return null;
        }

        $messages = [
            'create_tontine' => 'Nous avons besoin de vos informations pour créer votre tontine en toute sécurité.',
            'adhesion' => 'Complétez votre profil et vos pièces pour adhérer à une tontine.',
        ];

        return [
            'code' => 'PROFILE_INCOMPLETE',
            'message' => $messages[$action] ?? $messages['create_tontine'],
            'completeness' => $completeness,
        ];
    }

    private function present(mixed $value): bool
    {
        return trim((string) $value) !== '';
    }
}
