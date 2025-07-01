<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'montant',
        'tontine_id',
        'user_id',
        'transaction_id',
        'date_contribution'
    

    ];
    
    protected $dates = ['date_contribution'];
    
     public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tontine()
    {
        return $this->belongsTo(Tontine::class);
    }
}
