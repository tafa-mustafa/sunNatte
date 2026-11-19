<?php

namespace App\Notifications;

use App\Models\Tontine;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContributionDueNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Tontine $tontine,
        private string $dateVersement,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tontine_id' => $this->tontine->id,
            'date_versement' => $this->dateVersement,
            'type' => 'rappel_cotisation',
            'message' => 'Rappel : cotisation pour '.$this->tontine->nom.' dans 2 jours ('.$this->dateVersement.').',
        ];
    }
}
