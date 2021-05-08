<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeCandidate extends Model
{
    use HasFactory;
    protected $table="free_candidates";
    protected $fillable=['name','description','picture','election_id'];
    public $timestamps=false;

    public function election(){
        return $this->belongsTo(Party::class,'election_id');
    }
}
