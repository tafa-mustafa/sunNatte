<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tirelire extends Model
{
    
    
    protected $fillable = [
        'titre',
        'montant',
        'objectif',
        'date_debut',
        'date_fin',
        'user_id',
        'montant_objectif'
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function user()
    {

        return $this->belongsTo(User::class);
    }
}
