<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Election extends Model
{
    use HasFactory;
    public $timestamps=false;
    protected $casts = [
        'start_date' => 'datetime:Y-m-d H:i',
        'end_date' => 'datetime:Y-m-d H:i',
    ];
    protected $table="elections";
    protected $fillable = [
        'start_date',
        'end_date',
        'title',
        'description',
        'organizer_id',
        'municipal_id'
    ];
}
