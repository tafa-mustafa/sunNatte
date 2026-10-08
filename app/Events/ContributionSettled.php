<?php

namespace App\Events;

use App\Models\Contribution;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContributionSettled implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public Contribution $contribution)
    {
        if ($this->contribution->exists) {
            $this->contribution->loadMissing(['tontine:id,nom', 'user:id,nom,prenom']);
        }
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.'.$this->contribution->user_id),
            new PrivateChannel('tontine.'.$this->contribution->tontine_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'contribution.settled';
    }

    public function broadcastWith(): array
    {
        return [
            'contribution_id' => $this->contribution->id,
            'tontine_id' => $this->contribution->tontine_id,
            'user_id' => $this->contribution->user_id,
            'montant' => $this->contribution->montant,
            'statut' => $this->contribution->statut,
        ];
    }
}
