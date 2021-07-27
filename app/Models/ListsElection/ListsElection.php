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
}
