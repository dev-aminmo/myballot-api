<?php

namespace App\Models;

use App\Models\PluralityElection\PluralityElection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;
    protected $table="candidates";
    protected $fillable=['name','description','picture',"count","type"];
    protected $hidden=['count','type'];
    public $timestamps=false;

    public function election(){
        return $this->belongsTo(PluralityElection::class,'election_id');
    }
    public function list_candidate()
    {
        return $this->hasOne(ListCandidate::class,'id');
    }

  public function plurality_candidate()
    {
        return $this->hasOne(PluralityCandidate::class,'id');
    }

    public function getPictureAttribute($value)
    {
        if($value == null){
            return "https://res.cloudinary.com/dtvc2pr8i/image/upload/w_150,f_auto/v1627571884/myballot/users/Asset_1_cqcehd.png";

        }
        return $value;
    }


}
