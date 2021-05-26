<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddVotersRequest;
use App\Http\Requests\DeleteVoterRequest;
use App\Jobs\SendMailsJob;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\FreeCandidate;
use App\Models\PartisanCandidate;
use App\Models\Party;
use App\Models\PluralityElection\PluralityElection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Helpers\MyResponse;
class ElectionController extends Controller
{
    use MyResponse;

    function add_voters(AddVotersRequest $request)
    {
        $election_id= $request->election_id;
        try {
            $organizers = User::whereRoleIs(['organizer'])->get();
            $intersection = $organizers->intersect(User::whereIn('email', $request->emails)->get());
            if (count($intersection)) {
                $ad = [];
                $intersection->each(function ($org) use (&$ad) {
                    $ad[] = $org->email;
                    return $org->email;
                });
                return $this->returnValidationResponse($ad,422, "organizers cannot vote");
            }
            foreach ($request->emails as $email) {
                $u = User::where('email', $email)->first();

                if (!empty($u)) {
                    $hasBeenAdded = $u->elections()->where('election_id', $election_id)->exists();
                    if(!$hasBeenAdded){
                        $u->elections()->syncWithoutDetaching($election_id);
                        $data=['type'=>2,"email"=>$email];
                        $this->dispatch(new SendMailsJob($data));
                    }
                } else {
                    $pass = Str::random(6);
                    $user = User::create([
                            'email' => $email,
                            'password' => bcrypt($pass),]
                    );
                    $user->attachRole('voter');
                    $user->elections()->attach($request->election_id);
                    $data=['type'=>1,"email"=>$email,'password' => $pass];
                    $this->dispatch(new SendMailsJob($data));
                }
                return $this->returnSuccessResponse('voters added successfully');
            }
        } catch (\Exception  $exception) {
            return $this->returnErrorResponse();
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
        if ($this->isStarted($election_id) ||!$this->isOrganizer($election_id)) return  redirect('/');

        $election=Election::where('id',$election_id)->first();
        $data["data"] =$election->users()->where('election_id',$election_id)->get()->each(function($value){
          unset($value->pivot);
            return $value;
        });
        $data['message']="success";
        return response()->json($data,200);
    }
    function delete_voter(DeleteVoterRequest $request){
        $election_id= $request->election_id;
        $u = User::where('id',  $request->voter_id)->first();
        $election=$u->elections()->where('election_id', $election_id)->first();
       if ( !empty($election)){
           $election->pivot->delete();
       }
        return $this->returnSuccessResponse("voter deleted successfully");
    }
    function update(Request $request){
        $validation =  Validator::make($request->all(), [
            'election_id'=>'required|integer|exists:elections,id',
            'start_date'    => 'required|date|date_format:Y-m-d H:i|after_or_equal:now',
            'end_date'    => 'required|date|date_format:Y-m-d H:i|after:start_date',
            'title'=> 'string|min:2|max:255',
            'description'=> 'string|min:10|max:400'
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
        $election_id=$request->election_id;
        if ($this->isStarted($election_id) ||!$this->isOrganizer($election_id)) return  redirect('/');
        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $diff_in_minutes = $end->diffInMinutes($start);
        if ($diff_in_minutes < 5)  {
            return response()->json([
                "message"=>"the difference between start_date and end_date should be more than 5 minutes",
                "code"=>"202"
            ], 422);
        }
        try{
            Election::where('id',$election_id)->update($request->only(['start_date','end_date','title','description']));
            $data = ['message' => 'election updated successfully','code'=>201];
            return Response()->json($data,201);
        }catch ( \Exception  $exception){
            $response['error']='an error has occurred';
            $response['code']=400;
            return response()->json($response, 400);
        }
    }

}
