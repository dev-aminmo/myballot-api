<?php

namespace App\Models\PluralityElection;

use App\Models\FreeCandidate;
use App\Models\PartisanCandidate;
use App\Models\Party;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PluralityElection extends Model
{
    public $timestamps=false;
    protected $table="plurality_elections";
    protected $fillable = [
        'start_date',
        'end_date',
        'title',
        'description',
        'organizer_id',
    ];

    public function partisan_candidates()
    {
        return $this->hasMany(Party::class,'election_id');
    }
    public function free_candidates()
    {
        return $this->hasMany(FreeCandidate::class,'election_id');
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'plurality_election_user', 'user_id', 'plurality_election_id')     ->withPivot('voted');

    }
}
