<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Adhesion extends Model
{
    use HasFactory;

    protected $fillable = ['badge'];

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
