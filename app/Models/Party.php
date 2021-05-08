<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    use HasFactory;
    protected $table="parties";
public $timestamps=false;
    protected $fillable=['name','picture','election_id'];
    public function candidates()
    {
        return $this->hasMany(Candidate::class,'party_id');
    }
}
