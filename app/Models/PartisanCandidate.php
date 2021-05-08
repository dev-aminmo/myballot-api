<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartisanCandidate extends Model
{
    use HasFactory;
    protected $table="partisan_candidates";
    protected $fillable=['name','description','picture','party_id'];
    public $timestamps=false;

    public function party(){
        return $this->belongsTo(Party::class,'party_id');
    }
}
