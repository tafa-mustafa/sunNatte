<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\Notification;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationDispatcher
{
    public function __construct(
        private NotificationPreferenceService $prefs,
        private SmsGateway $sms,
    ) {
    }

    public function send(User $user, string $event, string $message, ?Notification $notification = null): void
    {
        if ($this->prefs->allows($user, $event, 'push')) {
            if ($notification) {
                $user->notify($notification);
            }
            if ($user->fcm_token) {
                app(FCMService::class)->send($user->fcm_token, $this->subject($event), $message);
            }
        }

        if ($this->prefs->allows($user, $event, 'sms') && $user->phone) {
            $this->sms->send($user->phone, $message);
        }

        if ($this->prefs->allows($user, $event, 'email') && $user->email) {
            try {
                Mail::to($user->email)->send(new NotificationMail($this->subject($event), $message));
            } catch (\Throwable $e) {
                Log::warning('Email notif échoué : '.$e->getMessage());
            }
        }
    }

    private function subject(string $event): string
    {
        return match ($event) {
            'cotisation_versee' => 'Sunu Natt — Paiement confirmé',
            'rappel_cotisation' => 'Sunu Natt — Rappel de cotisation',
            'distribution_mutuelle' => 'Sunu Natt — Fonds reçus',
            'mutuelle_closed' => 'Sunu Natt — Mutuelle clôturée',
            default => 'Sunu Natt',
        };
    }
}
