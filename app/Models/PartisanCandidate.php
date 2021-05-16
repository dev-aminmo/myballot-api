<?php

namespace App\Models;

use App\Models\ListsElection\PartisanElectionList;
use App\Models\PluralityElection\PluralityElection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartisanCandidate extends Model
{
    use HasFactory;
    protected $table="partisan_candidates";
    protected $fillable=['id'/*,'name','description','picture',*/,'party_id'];
    public $timestamps=false;

    public function party(){
        return $this->belongsTo(PluralityElection::class,'election_id');
    }
}
