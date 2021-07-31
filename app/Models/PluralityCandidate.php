<?php

namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PluralityCandidate extends Model
{
    use HasFactory;
    protected $table="plurality_candidates";
    protected $fillable=["id",/*'name','description','picture','election_id',*/'election_id'];
    public $timestamps=false;

    /*public function election(){
        return $this->belongsTo(PluralityElection::class,'election_id');
    }
    public function free_list(){
        return $this->belongsTo(FreeElectionList::class,'list_id');
    }*/

    public function candidate(){
        return $this->belongsTo(Candidate::class,'id');
    }
  /*  public static function boot() {
        parent::boot();
        static::deleting(function($plurality_candidate) { // before delete() method call this
            $plurality_candidate->candidate()->delete();
            return parent::delete();
            // do the rest of the cleanup...
        });
    }*/
/*    public static function boot()
    {
        User::observe(UserObserver::class);
    }*/

}
/*class UserObserver
{
    public function deleting(User $user)
    {
        $user->photos()->delete();
    }
}*/
