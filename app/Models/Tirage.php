<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tirage extends Model
{
    use HasFactory;
    
    protected $fillable = [
            'tontine_id',
            'user_id',
            'date_versement',
             'status'
           
        ];
        
        
     const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
        
         public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le modèle Tontine.
     */
    public function tontine()
    {
        return $this->belongsTo(Tontine::class);
    }
}
