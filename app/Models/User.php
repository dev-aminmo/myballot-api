<?php

namespace App\Models;

use App\Models\PluralityElection\PluralityElection;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Laratrust\Traits\LaratrustUserTrait;

class User extends Authenticatable
{
    use LaratrustUserTrait;
    use HasApiTokens,  HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'is_organizer'
    ];
    public $timestamps = false;
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function getAvatarAttribute($value)
    {
        if($value == null){

            return "https://res.cloudinary.com/dtvc2pr8i/image/upload/w_150,f_auto/v1627577895/myballot/users/user_znc23a.png";

        }
        return $value;
    }
    public function getisOrganizerAttribute($value)
    {
        if($value){
         return true;
        }
        return false;
    }
    public function ballots()
    {
        return $this->belongsToMany(Ballot::class, 'ballot_user', 'user_id', 'ballot_id')
            ->withPivot('voted');
    }
}
