<?php

namespace App\Models\ListsElection;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListsElection extends Model
{
    use HasFactory;
    protected $table="lists_elections";
    protected $fillable=[
        "id",
        /*'start_date',
        'end_date',
        'title',
        'description',
        'organizer_id',*/
        'seats_number'
    ];
  //  protected $hidden=['count'];
    public $timestamps=false;

    public function partisan_lists(){
        return $this->hasMany(PartisanElectionList::class,'election_id');
    }
    public function free_lists(){
        return $this->hasMany(ElectionList::class,'election_id');
    }
}
