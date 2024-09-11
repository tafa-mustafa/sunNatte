<?php

namespace App\Notifications;

use App\Models\Tontine;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TontineNotification extends Notification
{
    use Queueable;
    protected $tontine;
    protected $data;
    /**
     * Create a new notification instance.
     */
    public function __construct(Tontine $tontine, array $data)
    {
        $this->tontine = $tontine;
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $user= auth()->user();
        $user_name = $user->nom ?? '';
        $user_nam = $user->prenom ?? '';
        return [
        // 'tontine_id' => $this->tontine->id,
        
        'message'=>  $user_nam . $user_name. 'Vient d\' adherer a votre tontine'.  $this->tontine->nom,
        ...$this->data
        ];
    }
}
