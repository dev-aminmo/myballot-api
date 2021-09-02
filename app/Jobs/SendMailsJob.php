<?php

namespace App\Jobs;

use App\Mail\ElectionEnded;
use App\Mail\ElectionStarted;
use App\Mail\AddVoterMail;
use App\Mail\YouAreInvited;
use App\Models\Ballot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
  //  public $tries = 5;

public  $data;
    public function __construct($data)
    {
        //
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //

        switch ($this->data['type']) {
            case 1:

                $details = [
                    'email' => $this->data['email'],
                    'password' => $this->data['password'],
                    'ballot' =>$this->data['ballot'],
                    'voter_name' =>$this->data['voter_name'],
                ];

                Mail::to($details['email'])->send(new AddVoterMail($details));
                break;
            case 2:
                $details = [
                    'ballot' =>$this->data['ballot'],
                    'voter_name' =>$this->data['voter_name'],
                ];
                Mail::to($this->data['email'])->send(new YouAreInvited($details));
                break;
            case 3:
                $details = [
                    'ballot' =>$this->data['ballot'],

                    'voter_name' =>$this->data['voter_name'],

                ];
                Mail::to($this->data['email'])->send(new ElectionStarted($details));
                break;
            case 4:
                $details = [
                    'ballot' =>$this->data['ballot'],

                    'voter_name' =>$this->data['voter_name'],

                ];
                Mail::to($this->data['email'])->send(new ElectionEnded($details));
                break;
        }

    }
}
