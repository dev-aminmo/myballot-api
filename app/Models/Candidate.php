<?php

namespace App\Models;

use App\Models\PluralityElection\PluralityElection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;
    protected $table="candidates";
    protected $fillable=['name','description','picture',"count",'election_id'];
    public $timestamps=false;

    public function election(){
        return $this->belongsTo(PluralityElection::class,'election_id');
    }
    public function partisan_candidate()
    {
        return $this->hasOne(PartisanCandidate::class,'id');
    }

    public function free_candidate()
    {
        return $this->hasOne(FreeCandidate::class,'id');
    }


}
