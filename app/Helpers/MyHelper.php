<?php
namespace App\Helpers;
use App\Models\Ballot;
use Illuminate\Support\Carbon;

trait MyHelper {
    public static function isOrganizer($election_id)
    {
        $p=Ballot::where('id',$election_id)->first();
        if(empty($p)){
            return false;
        }
        if($p->organizer_id == auth()->user()['id']){
            return true;
        }
        return false;
    }
    public static function isStarted($election_id){

        $election=Ballot::where('id',$election_id)->first();
        if(empty($election)){
            return false;
        }
        $start = Carbon::parse($election->start_date);
        $before=Carbon::now()->isBefore($start);
        if($before){
            return false;
        }
        return true;
    }
    public static function isEnded($election_id){
        $election=Ballot::where('id',$election_id)->first();
        if(empty($election)){
            return false;
        }
        $end = Carbon::parse($election->end_date);
        $ended=Carbon::now()->isAfter($end);
        if($ended){
            return true;
        }
        return false;
    }
}
