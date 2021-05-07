<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Election extends Model
{
    use HasFactory;
    public $timestamps=false;
    protected $table="elections";
    protected $fillable = [
        'start_date',
        'end_date',
        'title',
        'description',
        'organizer_id',
    ];
}
