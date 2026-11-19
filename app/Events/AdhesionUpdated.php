<?php

namespace App\Events;

use App\Models\Adhesion;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdhesionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public Adhesion $adhesion)
    {
        if ($this->adhesion->exists) {
            $this->adhesion->loadMissing(['tontine:id,nom', 'user:id,nom,prenom']);
        }
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.'.$this->adhesion->user_id),
            new PrivateChannel('tontine.'.$this->adhesion->tontine_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'adhesion.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'adhesion_id' => $this->adhesion->id,
            'tontine_id' => $this->adhesion->tontine_id,
            'user_id' => $this->adhesion->user_id,
            'statut' => $this->adhesion->statut,
            'tontine' => $this->adhesion->tontine->nom ?? '',
        ];
    }
}
