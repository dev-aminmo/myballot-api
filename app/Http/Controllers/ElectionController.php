<?php

namespace App\Http\Controllers;

use App\Jobs\SendMailsJob;
use App\Models\Election;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Helpers\MyHelper;
class ElectionController extends Controller
{
    use MyHelper;
    function add_voters(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'election_id' => 'required|integer|exists:elections,id',
            'emails' => 'required|array|min:1|max:150',
            'emails.*' => 'email',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
        $election_id= $request->election_id;
        if ($this->isStarted($election_id) ||!$this->isOrganizer($election_id)) return  redirect('/');

        try {
            $organizers = User::whereRoleIs(['organizer'])->get();
            $intersection = $organizers->intersect(User::whereIn('email', $request->emails)->get());
            if (count($intersection)) {
                $ad = [];
                $intersection->each(function ($org) use (&$ad) {
                    $ad[] = $org->email;
                    return $org->email;
                });
                return response()->json([
                    "message" => "organizers cannot vote",
                    "emails" => $ad,
                    "code" => 422
                ], 422);
            }
            foreach ($request->emails as $email) {
                $u = User::where('email', $email)->first();

                if (!empty($u)) {
                    $hasBeenAdded = $u->elections()->where('election_id', $election_id)->exists();
                    if(!$hasBeenAdded){
                        $u->elections()->syncWithoutDetaching($election_id);
                        $data=['type'=>2,"email"=>$email];
                        $this->dispatch(new SendMailsJob($data));
                        //  Mail::to($email)->send(new YouAreInvited($details));
                    }
                } else {
                    $pass = Str::random(6);
                    $user = User::create([
                            'email' => $email,
                            'password' => bcrypt($pass),]
                    );
                    $user->attachRole('voter');
                    $user->elections()->attach($request->election_id);
                    $details = [
                        'email' => $email,
                        'password' => $pass
                    ];

                    // Mail::to($email)->send(new MyTestMail($details));

                    $data=['type'=>1,"email"=>$email,'password' => $pass];
                    $this->dispatch(new SendMailsJob($data));

                }
                $data = ['message' => 'voters added successfully','code'=>201];
                return Response()->json($data,201);
            }
        } catch (\Exception  $exception) {
            $response = ['message' => "error has occurred",
                "code" => "400"];
            return response()->json($exception->getTrace(), 400);
        }
    }
    function get_voters(Request $request){
        $validation = Validator::make($request->all(), [
            'election_id' => 'required|integer|exists:elections,id',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
        $election_id= $request->election_id;
        $election=Election::where('id',6)->first();
        return $election->users()->where('election_id',6)->get();
    }

}
