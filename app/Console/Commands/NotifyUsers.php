<?php

namespace App\Console\Commands;

use App\Jobs\SendMailsJob;
use App\Models\Ballot;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class NotifyUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send an email to users';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $now = date("Y-m-d H:i:00", strtotime(Carbon::now()));
        logger($now);

        $elections_starts = Ballot::where("start_date", $now)->get();
        $elections_ends = Ballot::where("end_date", $now)->get();
        if (!$elections_starts->isEmpty()) {
            foreach ($elections_starts as $election) {
                $organizer=User::find($election->organizer_id);
                $data = ['type' => 3, "email" => $organizer->email,"voter_name"=>$organizer->name,'ballot'=>$election->getAttributes()];
                dispatch(new SendMailsJob($data));
                $election->users()->where('election_id', $election->id)->get()->each(function ($user)use(&$election) {
                    $data = ['type' => 3, "email" => $user->email,'ballot'=>$election->getAttributes(),"voter_name"=>$user->name];
                    dispatch(new SendMailsJob($data));
                    return $user;
                });
            }


        }
        if (!$elections_ends->isEmpty()) {
            foreach ($elections_ends as $election) {
                $organizer=User::find($election->organizer_id);
                $data = ['type' => 4, "email" => $organizer->email,"voter_name"=>$organizer->name,'ballot'=>$election->getAttributes()];
                dispatch(new SendMailsJob($data));
                $election->users()->where('election_id', $election->id)->get()->each(function ($user)use(&$election) {
                    $data = ['type' => 4, "email" => $user->email,'ballot'=>$election->getAttributes(),"voter_name"=>$user->name];

                    dispatch(new SendMailsJob($data));
                    return $user;
                });
            }

        }
    }
}
