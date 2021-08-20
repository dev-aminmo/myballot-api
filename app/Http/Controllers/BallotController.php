<?php

namespace App\Http\Controllers;

use App\Helpers\MyHelper;
use App\Http\Requests\AddVotersRequest;
use App\Http\Requests\DeleteBallotRequest;
use App\Http\Requests\DeleteVoterRequest;
use App\Http\Requests\ResultRequest;
use App\Http\Requests\UpdateBallotRequest;
use App\Jobs\SendMailsJob;
use App\Models\Candidate;
use App\Models\Ballot;
use App\Models\ListsElection\ListsElection;
use App\Models\PluralityCandidate;
use App\Models\ListCandidate;
use App\Models\Party;
use App\Models\PluralityElection\PluralityElection;
use App\Models\Poll\Question;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
        if (!$this->isOrganizer($ballot_id)) return  redirect('/');

        $ballot=Ballot::where('id',$ballot_id)->first();
        $data["data"] =$ballot->users()->where('ballot_id',$ballot_id)->get()->each(function($value){
            $value->voted=(bool) $value->pivot->voted;

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
            Ballot::where('id',$request->ballot_id)->update($request->only(['start_date','end_date','title','description','seats_number']));
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
    function ballot(Request $request,$id){
        $data =Ballot::find($id);
        if(!empty($data)){
        $user=auth()->user();
        if($user->is_organizer){
            return $this->returnDataResponse($data);
        }else{
            $voted= DB::table('ballot_user')
                ->where([
                    ['user_id', $user->id],
                    ['ballot_id' , $data->id]])
                ->select('voted')
                ->get()->first();
            $data->voted=(bool) $voted->voted;
            return $this->returnDataResponse($data);
        }
    }else{
            return $this->returnErrorResponse("ballot not found");
        }


    }

function results(ResultRequest $request){

    $request->merge(['id' => $request->route('id')]);
    $validation =  Validator::make($request->all(), [
        'id'=>'required|integer|exists:ballots,id',
    ]);
    if ($validation->fails()) {
        return response()->json($validation->errors(), 422);
    }
    $ballot= Ballot::where('id',$request->id)->first();
    switch($ballot->type){
        case "plurality":
            $data=  cache()->remember("result_Ballot".$ballot->id,60*60*24,function()use (&$ballot){
                $data["candidates"]= PluralityCandidate::where('election_id',$ballot->id)->with('candidate')->get()->pluck("candidate")->sortByDesc('count')->values();
                $election_id =  $ballot->id;
                $seats_number= $ballot->seats_number;
                $data["candidates"]->each(function($candidate)use (&$seats_number){
                    $candidate->makeVisible("count");
                    if($seats_number>0){
                        $candidate->selected=true;
                        $seats_number--;
                    }else{
                        $candidate->selected=false;
                    }
                    return $candidate;

                });

                $data["added_voters"] =$ballot->users()->where('ballot_id',$election_id )->count();
                $data["vote_casted"] =$ballot->users()->where(['ballot_id'=>$election_id ,
                    'voted'=>true
                ])->count();
                $data["vote_ratio"] =  ( $data["added_voters"] == 0)?0: $data["vote_casted"]/  $data["added_voters"];
                $data["vote_ratio"] =(float) number_format((float) $data["vote_ratio"], 2, '.', '');
                $data["code"]=200;
              return $data;
          });
            return $this->returnDataResponse($data);
            break;
        case "lists":
            $data=  cache()->remember("result_Ballot".$ballot->id,60*60*24,function()use (&$ballot) {
                $data["added_voters"] = $ballot->users()->where('ballot_id', $ballot->id)->count();
                $data["vote_casted"] = $ballot->users()->where(['ballot_id' => $ballot->id,
                    'voted' => true
                ])->count();
                $data["vote_ratio"] = ($data["added_voters"] == 0) ? 0 : $data["vote_casted"] / $data["added_voters"];
                $data["vote_ratio"] = (float)number_format((float)$data["vote_ratio"], 2, '.', '');

                $data["lists"] = ListsElection::where("election_id", $ballot->id)->orderBy('count', 'DESC')->with("candidates.candidate")->get();
                $data["lists"]->each(function ($list) {
                    $list->candidates->transform(function ($candidate) {
                        $can = $candidate->candidate;
                        return $can;

                    });
                    return $list;
                });

                $seats_number = $ballot->seats_number;
                $data["lists"]->transform(function ($list) use (&$seats_number) {
                    if ($seats_number > 0) {
                        $list->selected = true;
                        $seats_number--;
                    } else {
                        $list->selected = false;
                    }
                    return $list;
                });
                return $data;
            });
            return $this->returnDataResponse($data);
            break;
        case "poll":
            $data=  cache()->remember("result_Ballot".$ballot->id,60*60*24,function()use (&$ballot) {
                $data["added_voters"] = $ballot->users()->where('ballot_id', $ballot->id)->count();
                $data["vote_casted"] = $ballot->users()->where(['ballot_id' => $ballot->id,
                    'voted' => true
                ])->count();
                $data["vote_ratio"] = ($data["added_voters"] == 0) ? 0 : $data["vote_casted"] / $data["added_voters"];
                $data["vote_ratio"] = (float)number_format((float)$data["vote_ratio"], 2, '.', '');

                $data["questions"] = Question::where("poll_id", $ballot->id)->with("answers")->get();
            });
            return $this->returnDataResponse($data);
            break;

    }
}
public function delete(DeleteBallotRequest $request,$id){
        Ballot::find($id)->delete();
    return $this->returnSuccessResponse("ballot deleted successfully");
}
}
