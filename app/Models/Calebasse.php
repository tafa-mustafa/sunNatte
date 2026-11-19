<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Calebasse extends Model
{
    protected $fillable = [
        'createur_id', 'nom', 'description', 'type',
        'objectif', 'date_echeance', 'statut', 'code_invitation',
    ];

    protected $casts = [
        'objectif' => 'float',
        'date_echeance' => 'date',
    ];

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'createur_id');
    }

    public function membres(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'calebasse_user')
            ->withPivot('badge')
            ->withTimestamps();
    }

    public function cotisations(): HasMany
    {
        return $this->hasMany(CalebasseCotisation::class);
    }

    public function totalCollecte(): float
    {
        return (float) $this->cotisations()->where('statut', 'succeeded')->sum('montant');
    }

    public function isCreateur(User $user): bool
    {
        return (int) $this->createur_id === (int) $user->id;
    }
}
