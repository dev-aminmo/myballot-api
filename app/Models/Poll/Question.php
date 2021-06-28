<?php

namespace App\Models\Poll;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $fillable=['value','type_id',	'poll_id'];
    public $timestamps=false;
    public function answers()
    {
        return $this->hasMany(Answer::class,'question_id');
    }
}
