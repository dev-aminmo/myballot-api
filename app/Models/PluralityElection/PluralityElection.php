<?php

namespace App\Models\PluralityElection;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PluralityElection extends Model
{
    public $timestamps=false;
    protected $table="plurality_elections";
    protected $fillable = [
        'start_date',
        'end_date',
        'title',
        'description',
        'organizer_id',
    ];
}
