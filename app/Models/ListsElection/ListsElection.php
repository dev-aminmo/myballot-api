<?php

namespace App\Models\ListsElection;

use App\Models\ListCandidate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListsElection extends Model
{
    use HasFactory;
    protected $table="lists_elections";
    protected $fillable=['name'	,'picture'	,'program',	'count'	,'election_id',   'seats_number'];

  //  protected $hidden=['count'];
    public $timestamps=false;


    public function candidates(){
        return $this->hasMany(ListCandidate::class,'list_id');
    }
    public function getPictureAttribute($value)
    {
        if($value == null){

            return "https://res.cloudinary.com/dtvc2pr8i/image/upload/w_150,f_auto/v1627577895/myballot/users/user_znc23a.png";

        }
        return $value;
    }
}
