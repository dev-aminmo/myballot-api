<?php

namespace App\Models\ListsElection;

use App\Models\Party;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartisanElectionList extends Model
{
    use HasFactory;
    protected $fillable=['name'	,'picture'	,'program',	'count'	,'election_id'];
    protected $hidden=['count'];
    public $timestamps=false;
    public function parties(){
        return $this->hasMany(Party::class,'list_id');
    }
    public function party(){
        return $this->hasOne(Party::class,'list_id');
    }
    public function election(){
        return $this->belongsTo(ListsElection::class,'election_id');
    }
}
