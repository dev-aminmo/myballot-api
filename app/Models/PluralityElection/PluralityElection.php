<?php

namespace App\Models\PluralityElection;

use App\Models\PluralityCandidate;
use App\Models\ListCandidate;
use App\Models\Party;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PluralityElection extends Model
{
    public $timestamps=false;
    protected $table="plurality_elections";
    protected $fillable = [
        'id',
        /*'start_date',
        'end_date',
        'title',
        'description',*/
        'seats_number',
    ];
}
