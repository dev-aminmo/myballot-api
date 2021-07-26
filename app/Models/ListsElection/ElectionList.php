<?php

namespace App\Models\ListsElection;

use App\Models\Candidate;
use App\Models\PluralityCandidate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectionList extends Model
{
    use HasFactory;
    protected $table="election_lists";
    protected $fillable=['name'	,'picture'	,'program',	'count'	,'election_id'];
   // protected $hidden=['count'];
    public $timestamps=false;

    public function free_candidates(){
        return $this->hasMany(PluralityCandidate::class,'list_id');
    }
    public function candidates(){
        return $this->hasManyThrough(Candidate::class,PluralityCandidate::class,'list_id','id');
    }
}
