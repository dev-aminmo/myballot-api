<?php

namespace App\Models\Poll;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;
    protected $fillable=['value','count','question_id'];
    public $timestamps=false;

    public function question(){
        return $this->belongsTo(Question::class,'question_id');
    }
}
