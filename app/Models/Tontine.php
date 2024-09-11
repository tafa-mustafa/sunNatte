<?php

namespace App\Models;

use App\Models\{Adhesion,User};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tontine extends Model
{
    use HasFactory;

    
        protected $fillable = [
            'nom',
            'nombre_personne',
            'type',
            'duree',
            'montant',
            'tirage',
            'statut',
            'code_adhesion',
            'materiel_id',
            'date_demarrage',
            'date_fin',
            'description',
        ];

      
    public function users(): BelongsToMany

    {

        return $this->belongsToMany(User::class, 'adhesions', 'tontine_id', 'user_id');

    }

     public function adhesions():hasMany
    {
        return $this->hasMany(Adhesion::class);
    }

    public function materiel(): BelongsTo
    {
        return $this->belongsTo(Materiel::class);
    }
}
