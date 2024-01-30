<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Materiel extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'image',
        

    ];

    public function tontines(): HasMany
    {
        return $this->hasMany(Tontine::class, 'materiel_id');
    }
}
