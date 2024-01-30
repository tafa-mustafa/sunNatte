<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TontineWithMembresResource extends JsonResource
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
            // Ajoutez d'autres champs si nécessaire
            'utilisateurs' => UserResource::collection($this->whenLoaded('users')),
        ];    }
}
