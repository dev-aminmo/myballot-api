<?php

namespace App\Http\Controllers\PluralityElection;

use App\Http\Controllers\Controller;
use App\Http\Requests\PluralityElection\CreatePluralityElectionRequest;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\FreeCandidate;
use App\Models\PartisanCandidate;
use App\Models\Party;
use App\Models\PluralityElection\PluralityElection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Helpers\MyHelper;
use App\Helpers\MyResponse;

class PluralityElectionController extends Controller
{
use MyHelper;
use MyResponse;
    function create(CreatePluralityElectionRequest $request){
        $candidates_count=0;
        if (!empty($request->free_candidates)) {
            $candidates_count +=count($request->free_candidates);
        }
        if (!empty($request->parties)) {
            foreach($request->parties as $party){
                $candidates_count += count(  $party['candidates']);
            }
        }
        if ($candidates_count<2) {
            return  $this->returnValidationResponse(["the minimum number of candidates is 2"]);
        }

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $diff_in_minutes = $end->diffInMinutes($start);
        if ($diff_in_minutes < 5)  {
            return  $this->returnValidationResponse(["the difference between start_date and end_date should be more than 5 minutes"]);
        }
        try{
            $id= auth()->user()['id'];
            $allData = $request->all();
            $allData['organizer_id']=$id;
            $election_id=Election::create($allData)->id;
            PluralityElection::create([
                'id'=>$election_id
            ]);
            if (!empty($request->parties)) {
                foreach($request->parties as $party){
                    $party_id = Party::create(['name'=> $party['name'],
                        'election_id'=>$election_id])->id;
                    foreach($party['candidates']as $candidate){
                       $candidate_id= Candidate::create([ 'name'=> $candidate['name'],
                           'description'=>(!empty($candidate['description'])) ? $candidate['description'] : null,
                           "election_id"=>$election_id
                       ])->id;
                        PartisanCandidate::create([
                                 'id'=>$candidate_id,
                                'party_id'=>$party_id,
                            ]
                        );
                    }
                }
            }
            if (!empty($request->free_candidates)) {
                foreach($request->free_candidates as $candidate){
                    $candidate_id= Candidate::create([ 'name'=> $candidate['name'],
                        'description'=>(!empty($candidate['description'])) ? $candidate['description'] : null,
                        "election_id"=>$election_id
                    ])->id;
                    FreeCandidate::create([
                          "id"=>$candidate_id
                        ]
                    );
                }
            }
            $data = ['message' => 'election created successfully','code'=>201];
            return Response()->json($data,201);
        }catch ( \Exception  $exception){
            return $this->returnErrorResponse();
        }
    }
    function vote(Request $request){
        $validation =  Validator::make($request->all(), [
            'election_id'=>'required|integer|exists:elections,id',
            'candidate_id'=>'required|integer|exists:candidates,id'
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
        $election_id= $request->election_id;
        if (!$this->isStarted($election_id)|| $this->isEnded($election_id)) return  redirect('/');
        $user_id=auth()->user()['id'];
        $user=User::where("id",$user_id)->first();
        $is_voter =  $user->elections()->where('election_id', $election_id)->first();
        $candidate=Candidate::where([['id',$request->candidate_id], ['election_id', $election_id],])->first();
        if(!empty($candidate) &&$is_voter){
            $voted =   $is_voter->pivot->voted;
            if( $voted){
              $data = ['message' => 'vote already casted','code'=>422];
              return response()->json($data, 422);
           }
        $candidate->update(['count'=> DB::raw('count+1'),]);
        $user->elections()->updateExistingPivot($election_id, ['voted'=>true]);
        $data = ['message' => 'vote casted successfully','code'=>201];
        return Response()->json($data,201);
        }else{
            return  redirect('/');
        }
        }
    function results(Request $request){
        $request->merge(['id' => $request->route('id')]);
      $validation =  Validator::make($request->all(), [
            'id'=>'required|integer|exists:elections,id',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
        $data["candidates"]=Candidate::where('election_id',$request->id)->orderBy('count', 'DESC')->limit(3)->with(['partisan_candidate'=> function ($query) {
              $query->with('party');
        }])->get()->transform(function ($value){
             $data=$value;
             if (!empty($value->partisan_candidate)){
                 $data-> party=$value->partisan_candidate->party;
                 unset($data->party->list_id);
             }else{
                 $data-> party=null;
             }
             unset($data->partisan_candidate);
             return $data;
     });
        $election_id =  $request->id;
       $election=Election::where('id',$request->id)->first();
       $data["added_voters"] =$election->users()->where('election_id',$election_id )->count();
       $data["vote_casted"] =$election->users()->where(['election_id'=>$election_id ,
           'voted'=>true
       ])->count();
        $data["vote_ratio"] =  ( $data["added_voters"] == 0)?0: $data["vote_casted"]/  $data["added_voters"];
        $data["vote_ratio"] =(float) number_format((float) $data["vote_ratio"], 2, '.', '');
        $data["code"]=200;
        return Response()->json($data,200);
    }
}

