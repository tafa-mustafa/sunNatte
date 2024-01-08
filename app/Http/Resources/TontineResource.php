<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TontineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
        'id' => $this->id,
        'nom' => $this->nom,
        'nombre_personne' => $this->nombre_personne,
        'type' =>$this->type,
        'date_debut' =>$this->date_debut,
        'date_fin' =>  $this->date_fin,
        'user_id' => $this->user_id,
        'adhesion_id' => $this->adhesion_id,
      ];
    }
}
