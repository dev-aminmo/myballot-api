<?php

namespace App\Models;

use App\Models\ListsElection\ListsElection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ballot extends Model
{
    use HasFactory;
    public $timestamps=false;
    protected $table="ballots";
    protected $hidden=['seats_number'];
    protected $fillable = [
        'start_date',
        'end_date',
        'title',
        "type",
        'seats_number',
        'description',
        'organizer_id',
    ];


    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('voted');

    }
    public function getTypeAttribute($value)
    {
      switch($value){
          case 1:$v="plurality";break;
          case 2:$v="lists";break;
          case 3:$v="poll";break;
      }
        return $v;
    }
      public static function boot() {
      parent::boot();
      static::deleting(function($ballot) { // before delete() method call this

          switch($ballot->type){
              case "plurality":
                  $candidates = PluralityCandidate::where('election_id',$ballot->id)->get();
                  $candidates->each(function ($candidate){
                      $candidate->candidate()->delete();
                  });
                  ;break;
              case "lists":
                  $candidates =  ListsElection::where("election_id",$ballot->id)->with("candidates.candidate")->get()->pluck("candidates.*.candidate")->collapse();
                  $candidates->each(function($candidate){
                      $candidate->delete();
                  });
                  break;
              case "poll":
                  ;break;
          }

      });
  }

}
