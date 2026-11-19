<?php

namespace App\Notifications;

use App\Models\Mutuelle;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MutuelleNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Mutuelle $mutuelle,
        private string $message,
        private string $kind = 'mutuelle',
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'mutuelle_id' => $this->mutuelle->id,
            'type' => $this->kind,
            'message' => $this->message,
        ];
    }
}
