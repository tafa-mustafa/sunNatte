<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalebasseCotisation extends Model
{
    protected $fillable = [
        'calebasse_id', 'user_id', 'montant', 'statut', 'session_id', 'provider',
    ];

    protected $casts = [
        'montant' => 'float',
    ];

    public function calebasse(): BelongsTo
    {
        return $this->belongsTo(Calebasse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
