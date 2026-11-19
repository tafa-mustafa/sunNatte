<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mutuelle extends Model
{
    protected $fillable = [
        'createur_id',
        'nom',
        'objectif',
        'taux_interet',
        'duree_mois',
        'statut',
        'fonds_credite',
        'session_id',
    ];

    protected $casts = [
        'taux_interet' => 'float',
        'fonds_credite' => 'float',
    ];

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'createur_id');
    }

    public function beneficiaires(): HasMany
    {
        return $this->hasMany(MutuelleBeneficiaire::class);
    }

    public function mouvements(): HasMany
    {
        return $this->hasMany(MutuelleMouvement::class);
    }
}
