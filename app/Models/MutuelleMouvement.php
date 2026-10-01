<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutuelleMouvement extends Model
{
    protected $fillable = [
        'mutuelle_id',
        'beneficiaire_id',
        'type',
        'montant',
        'provider',
        'session_id',
        'statut',
    ];

    public function mutuelle(): BelongsTo
    {
        return $this->belongsTo(Mutuelle::class);
    }
}
