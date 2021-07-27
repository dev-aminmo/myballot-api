<?php

namespace App\Models;

use App\Models\ListsElection\ListsElection;
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

    public function listx(){
        return $this->belongsTo(ListsElection::class,'list_id');
    }
    public function candidate()
    {
        return $this->belongsTo(Candidate::class,'id');
    }
}
