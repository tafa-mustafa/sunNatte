<?php

namespace App\Notifications;

use App\Models\Contribution;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContributionPaidNotification extends Notification
{
    use Queueable;

    public function __construct(private Contribution $contribution, private string $role = 'payer')
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $tontine = $this->contribution->tontine->nom ?? '';
        $montant = $this->contribution->montant;

        return [
            'contribution_id' => $this->contribution->id,
            'tontine_id' => $this->contribution->tontine_id,
            'type' => $this->role === 'owner' ? 'cotisation_recue' : 'cotisation_versee',
            'message' => $this->role === 'owner'
                ? "Cotisation de {$montant} FCFA reçue sur {$tontine}."
                : "Paiement de {$montant} FCFA confirmé pour {$tontine}.",
        ];
    }
}
