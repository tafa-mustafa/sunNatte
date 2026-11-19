<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Adhesion extends Model
{
    use HasFactory;

    protected $fillable = [
        'badge',
        'ordre_tirage',
        'tontine_id',
        'user_id',
        'statut',
        'motif_rejet',
        'reviewed_at',
        'reviewed_by',
    ];

    public $timestamps = true;


    public function tontine(): BelongsTo
    {
        return $this->belongsTo(Tontine::class);
    }

    public function user(): BelongsTo

    {
        return $this->belongsTo(User::class);
    }
}
