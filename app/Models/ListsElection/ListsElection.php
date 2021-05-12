<?php

namespace App\Models\ListsElection;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListsElection extends Model
{
    use HasFactory;
    protected $table="lists_elections";
    protected $fillable=[
        'start_date',
        'end_date',
        'title',
        'description',
        'organizer_id',
        'count'
    ];
    protected $hidden=['count'];
    public $timestamps=false;
}
