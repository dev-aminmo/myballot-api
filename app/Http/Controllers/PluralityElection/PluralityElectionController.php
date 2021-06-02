<?php

namespace App\Http\Controllers\PluralityElection;

use App\Http\Controllers\Controller;
use App\Http\Requests\PluralityElection\CreatePluralityElectionRequest;
use App\Http\Requests\PluralityElection\VotePluralityElectionRequest;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\FreeCandidate;
use App\Models\PartisanCandidate;
use App\Models\Party;
use App\Models\PluralityElection\PluralityElection;
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
        $id= auth()->user()['id'];
            $allData = $request->all();
            $allData['organizer_id']=$id;
            $election_id=Election::create($allData)->id;
            PluralityElection::create([
                'id'=>$election_id,
                'seats_number'=>(!empty($request->seats_number)) ? $request->seats_number : 1,
            ]);
            if (!empty($request->parties)&& !empty($request->type) == 1) {
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
            return $this->returnSuccessResponse('election created successfully');
    }
    function vote(VotePluralityElectionRequest $request){
        $election_id= $request->election_id;
        $user=auth()->user();
        $is_voter =  $user->elections()->where('election_id', $election_id)->first();
        $candidate=Candidate::where([['id',$request->candidate_id], ['election_id', $election_id],])->first();
        if(!empty($candidate) &&$is_voter){
            $voted =   $is_voter->pivot->voted;
            if( $voted){
                return  $this->returnErrorResponse('vote already casted');
           }
        $candidate->update(['count'=> DB::raw('count+1'),]);
        $user->elections()->updateExistingPivot($election_id, ['voted'=>true]);
            return  $this->returnSuccessResponse('vote casted successfully');
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
       $election= Election::where('id',$request->id)->first();
        if(json_decode($election->result,true ) == null){
      if($election->type == 1){
          $data["candidates"]= Candidate::where('election_id',$request->id)->orderBy('count', 'DESC')->with(['partisan_candidate'=> function ($query) {
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
      }else{
          $data["candidates"]=Candidate::where('election_id',$request->id)->orderBy('count', 'DESC')->get();

      }
            $election_id =  $election->id;

         $seats_number= PluralityElection::find($election_id)->seats_number;
            $data["candidates"]->each(function($candidate)use (&$seats_number){
                if($seats_number>0){
                    $candidate->selected=true;
                    $seats_number--;
                }else{
                    $candidate->selected=false;
                }
                return $candidate;

            });

       $data["added_voters"] =$election->users()->where('election_id',$election_id )->count();
       $data["vote_casted"] =$election->users()->where(['election_id'=>$election_id ,
           'voted'=>true
       ])->count();
        $data["vote_ratio"] =  ( $data["added_voters"] == 0)?0: $data["vote_casted"]/  $data["added_voters"];
        $data["vote_ratio"] =(float) number_format((float) $data["vote_ratio"], 2, '.', '');
        $data["code"]=200;
        $election->result=$data;
        $election->save();
        return Response()->json($data,200);}
       else{
          $data=  json_decode($election->result,true );
           return Response()->json( $data,200);
       }
    }

}

