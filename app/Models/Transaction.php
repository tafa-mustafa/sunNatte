<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    
     protected $fillable = [
        'tirelire_id',
        'session_id',
        'montant',
        'statut',
        'commission',
        'user_id',
        'type',
    ];

    public function tirelire()
    {
        return $this->belongsTo(Tirelire::class);
    }
    
     public function user()
    {
        return $this->belongsTo(User::class);
    }
}
