<?php

namespace App\Models;

use App\Models\ListsElection\PartisanElectionList;
use App\Models\PluralityElection\PluralityElection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListCandidate extends Model
{
    use HasFactory;
    protected $table="lists_candidates";
    protected $fillable=['id'/*,'name','description','picture',*/,'list_id'];
    public $timestamps=false;

    public function party(){
        return $this->belongsTo(Party::class,'party_id');
    }
    public function candidate()
    {
        return $this->belongsTo(Candidate::class,'id');
    }
    public function carOwner()
    {
        return $this->hasOneThrough(
            Owner::class,
            Car::class,
            'mechanic_id', // Foreign key on the cars table...
            'car_id', // Foreign key on the owners table...
            'id', // Local key on the mechanics table...
            'id' // Local key on the cars table...
        );
    }
}
