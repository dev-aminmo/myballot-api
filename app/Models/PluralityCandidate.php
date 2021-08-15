<?php

namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PluralityCandidate extends Model
{
    use HasFactory;
    protected $table="plurality_candidates";
    protected $fillable=["id",'election_id'];
    public $timestamps=false;


    public function candidate(){
        return $this->belongsTo(Candidate::class,'id');
    }

}
