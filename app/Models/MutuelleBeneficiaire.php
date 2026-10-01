<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutuelleBeneficiaire extends Model
{
    protected $fillable = [
        'mutuelle_id',
        'user_id',
        'nom',
        'phone',
        'montant_alloue',
        'montant_du',
        'montant_rembourse',
        'statut',
    ];

    protected $casts = [
        'montant_alloue' => 'float',
        'montant_du' => 'float',
        'montant_rembourse' => 'float',
    ];

    public function mutuelle(): BelongsTo
    {
        return $this->belongsTo(Mutuelle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restant(): float
    {
        return max(0, (float) $this->montant_du - (float) $this->montant_rembourse);
    }
}
