<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\{Tontine, Adhesion};
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
   protected $fillable = ['nom','password', 'prenom',"phone", 'profession', 'adresse', 'num_cni', 'num_passport', 'bank', 'phone2', 'avatar','reset_code','badge'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

     CONST ROLE_ADMIN = 1;
    CONST ROLE_USER = 2;

   
    public function roles()
    {
      return $this->hasMany(Role::class);
    }

        public function tontines(): BelongsToMany
    {

        return $this->belongsToMany(Tontine::class, 'adhesions', 'user_id','tontine_id');

    }
    
  public function adhesions(): hasMany
    {
        return $this->hasMany(Adhesion::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'user_id');
    }
}
