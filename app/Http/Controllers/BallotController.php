<?php

namespace App\Http\Controllers;

use App\Helpers\MyHelper;
use App\Http\Requests\AddVotersRequest;
use App\Http\Requests\DeleteVoterRequest;
use App\Http\Requests\UpdateBallotRequest;
use App\Jobs\SendMailsJob;
use App\Models\Candidate;
use App\Models\Ballot;
use App\Models\PluralityCandidate;
use App\Models\ListCandidate;
use App\Models\Party;
use App\Models\PluralityElection\PluralityElection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Helpers\MyResponse;
class BallotController extends Controller
{
    use MyResponse;
    use MyHelper;

    function add_voters(AddVotersRequest $request)
    {
        $ballot_id= $request->ballot_id;

        try {
            $emails=[];
            foreach ($request->voters as $voter){
                $emails[]=$voter["email"];
            }

            $organizers = User::where("is_organizer",1)->get();
            $intersection = $organizers->intersect(User::whereIn('email', $emails)->get());

            if (count($intersection)) {
                $ad = [];
                $intersection->each(function ($org) use (&$ad) {
                    $ad[] = $org->email;
                    return $org->email;
                });
                return $this->returnValidationResponse($ad,422, "organizers cannot vote");
            }

            foreach ($request->voters as $voter) {

                $email=$voter["email"];
                $name=(isset($voter["name"]))?$voter["name"]:false;

                $u = User::where('email', $email)->first();
                if (!empty($u)) {
                    $hasBeenAdded = $u->ballots()->where('ballot_id', $ballot_id)->exists();
                    if(!$hasBeenAdded){
                        $u->ballots()->syncWithoutDetaching($ballot_id);
                        $data=['type'=>2,"email"=>$email];
                        $this->dispatch(new SendMailsJob($data));
                    }
                } else {
                    $pass = Str::random(6);
                    $user = User::create([
                            'email' => $email,
                            'password' => bcrypt($pass),
                            'name'=>($name)?$name:NULL,
                            'is_organizer' =>0
                            ]
                    );

                    $user->ballots()->attach($request->ballot_id);
                    $data=['type'=>1,"email"=>$email,'password' => $pass];
                    $this->dispatch(new SendMailsJob($data));
                } }
                return $this->returnSuccessResponse('voters added successfully');

        } catch (\Exception  $exception) {

            return $this->returnErrorResponse();
        }
    }

    function get_voters(Request $request){
        $request->merge(['ballot_id' => $request->route('id')]);

        $validation = Validator::make($request->all(), [
            'ballot_id' => 'required|integer|exists:ballots,id',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }

        $ballot_id= $request->ballot_id;
        if ($this->isStarted($ballot_id) ||!$this->isOrganizer($ballot_id)) return  redirect('/');

        $ballot=Ballot::where('id',$ballot_id)->first();
        $data["data"] =$ballot->users()->where('ballot_id',$ballot_id)->get()->each(function($value){
          unset($value->pivot);
            return $value;
        });
        $data['message']="success";
        return response()->json($data,200);
    }
    function delete_voter(DeleteVoterRequest $request){
        $ballot_id= $request->ballot_id;
        $u = User::find( $request->voter_id);
        $ballot=$u->ballots()->where('ballot_id', $ballot_id)->first();
       if ( !empty($ballot)){
           $ballot->pivot->delete();
       }
        return $this->returnSuccessResponse("voter deleted successfully");
    }
    function update(UpdateBallotRequest $request){
        try{
            Ballot::where('id',$request->ballot_id)->update($request->only(['start_date','end_date','title','description']));
            return $this->returnSuccessResponse("ballot updated successfully");
        }catch ( \Exception  $exception){
            return $this->returnErrorResponse();
        }
    }
    function ballots(Request $request){
        $user=auth()->user();
        if($user->is_organizer){
            $data =Ballot::where('organizer_id',$user->id)->get();
            return $this->returnDataResponse($data);
        }else{
            $data =  $user->ballots()->where('user_id',$user->id)->get()->transform(function($value){
                $value->voted=(bool) $value->pivot->voted;
                unset($value->pivot);
                return $value;
            });
            return $this->returnDataResponse($data);
        }

    }


}
