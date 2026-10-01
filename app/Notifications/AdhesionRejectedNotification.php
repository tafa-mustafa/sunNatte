<?php

namespace App\Notifications;

use App\Models\Adhesion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdhesionRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(private Adhesion $adhesion)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $nom = $this->adhesion->tontine->nom ?? '';

        return [
            'adhesion_id' => $this->adhesion->id,
            'tontine_id' => $this->adhesion->tontine_id,
            'statut' => 'rejected',
            'type' => 'adhesion_rejetee',
            'message' => 'Demande refusée : '.$this->adhesion->motif_rejet,
            'motif' => $this->adhesion->motif_rejet,
            'tontine' => $nom,
        ];
    }
}
