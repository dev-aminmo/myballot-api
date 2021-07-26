<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ballot extends Model
{
    use HasFactory;
    public $timestamps=false;
    protected $table="elections";
    protected $fillable = [
        'start_date',
        'end_date',
        'title',
        "type",
        'description',
        'organizer_id',
    ];
    protected $hidden = [
        'result',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('voted');

    }

}