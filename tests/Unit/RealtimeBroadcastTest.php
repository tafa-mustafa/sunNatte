<?php

namespace Tests\Unit;

use App\Events\AdhesionUpdated;
use App\Events\ContributionSettled;
use App\Mail\NotificationMail;
use App\Models\Adhesion;
use App\Models\Contribution;
use Illuminate\Broadcasting\PrivateChannel;
use Tests\TestCase;

class RealtimeBroadcastTest extends TestCase
{
    public function test_adhesion_updated_broadcasts_on_user_and_tontine(): void
    {
        $adhesion = new Adhesion([
            'user_id' => 4,
            'tontine_id' => 12,
            'statut' => 'approved',
        ]);

        $event = new AdhesionUpdated($adhesion);
        $channels = $event->broadcastOn();

        $this->assertSame('adhesion.updated', $event->broadcastAs());
        $this->assertCount(2, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertSame('private-user.4', $channels[0]->name);
        $this->assertSame('private-tontine.12', $channels[1]->name);
    }

    public function test_contribution_settled_broadcasts_on_user_and_tontine(): void
    {
        $contribution = new Contribution([
            'user_id' => 8,
            'tontine_id' => 3,
            'montant' => 5000,
            'statut' => 'succeeded',
        ]);

        $event = new ContributionSettled($contribution);

        $this->assertSame('contribution.settled', $event->broadcastAs());
        $this->assertSame('private-user.8', $event->broadcastOn()[0]->name);
        $this->assertSame(5000, $event->broadcastWith()['montant']);
    }

    public function test_notification_mail_uses_html_body(): void
    {
        $mail = new NotificationMail('Sunu Natt', "Ligne 1\nLigne 2");
        $built = $mail->content();

        $this->assertSame('Sunu Natt', $mail->envelope()->subject);
        $this->assertStringContainsString('Ligne 1', $built->htmlString);
    }
}
