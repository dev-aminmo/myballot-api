<?php

namespace App\Jobs;

use App\Mail\MyTestMail;
use App\Mail\YouAreInvited;
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
        if($this->data['type'] ==1){
            $details = [
                'email' => $this->data['email'],
                'password' => $this->data['password']
            ];

            Mail::to($details['email'])->send(new MyTestMail($details));
        }else{
            $details = [
                'link' => "https://www.google.com/",
            ];
            Mail::to($this->data['email'])->send(new YouAreInvited($details));
        }



    }
}
