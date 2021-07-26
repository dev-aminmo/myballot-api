<?php

namespace App\Models;

use App\Models\ListsElection\PartisanElectionList;
use App\Models\PluralityElection\PluralityElection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    use HasFactory;
    protected $table="parties";
public $timestamps=false;
    protected $fillable=['name','picture','election_id','list_id'];
    public function candidates()
    {
       // return $this->hasMany(PartisanCandidate::class,'party_id');
     //   public function candidates(){
        return $this->hasManyThrough(Candidate::class,ListCandidate::class,'party_id','id');
   // }
    }
    public function plurality_election()
    {
        return $this->belongsTo(PluralityElection::class,'election_id');
    }
    public function partisan_list(){
        return $this->belongsTo(PartisanElectionList::class,'list_id');
    }
}
