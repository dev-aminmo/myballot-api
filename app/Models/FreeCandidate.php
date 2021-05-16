<?php

namespace App\Models;

use App\Models\ListsElection\FreeElectionList;
use App\Models\PluralityElection\PluralityElection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeCandidate extends Model
{
    use HasFactory;
    protected $table="free_candidates";
    protected $fillable=["id",/*'name','description','picture','election_id',*/'list_id'];
    public $timestamps=false;

    public function election(){
        return $this->belongsTo(PluralityElection::class,'election_id');
    }
    public function free_list(){
        return $this->belongsTo(FreeElectionList::class,'list_id');
    }

}
