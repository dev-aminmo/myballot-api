<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;
    protected $fillable=['name','description','picture','party_id','election_id'];
    public $timestamps=false;

    public function party(){
        return $this->belongsTo(Party::class,'party_id');
    }
    public function election(){
        return $this->belongsTo(Party::class,'election_id');
    }
}
