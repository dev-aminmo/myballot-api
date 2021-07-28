<?php

namespace App\Http\Controllers\ListsElection;

use App\Helpers\MyResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateListsElectionRequest;
use App\Http\Requests\PluralityElection\VotePluralityElectionRequest;
use App\Http\Requests\UpdateElectionList;
use App\Models\Candidate;
use App\Models\Ballot;
use App\Models\PluralityCandidate;
use App\Models\ListsElection\ElectionList;
use App\Models\ListsElection\ListsElection;
use App\Models\ListsElection\PartisanElectionList;
use App\Models\ListCandidate;
use App\Models\Party;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ListsElectionController extends Controller
{
    use MyResponse;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    function create(CreateListsElectionRequest $request){
        try{
            $id= auth()->user()['id'];
            $allData = $request->all();
            $allData['organizer_id']=$id;
            $allData['type']=2;
            $election_id=Ballot::create($allData)->id;




                foreach($request->lists as $list){
                    $list_id= ListsElection::create([
                       "name"=>$list["name"],
                       "program"=>$list["program"],
                       'seats_number'=> $request->seats_number,
                        "election_id"=>$election_id
                   ])->id;
                foreach($list["candidates"] as $candidate){
                    $candidate_id= Candidate::create([
                        'name'=> $candidate['name'],
                        'description'=>(!empty($candidate['description'])) ? $candidate['description'] : null,
                        "type"=>2
                    ])->id;
                    ListCandidate::create([
                        'id'=>$candidate_id,
                        'list_id'=>$list_id
                        ]
                    );
                }
                }


            return $this->returnSuccessResponse('election created successfully');
        }catch ( \Exception  $exception){
           return $this->returnErrorResponse($exception->getTrace());
        }
    }
    function vote(VotePluralityElectionRequest $request){
        $ballot_id= $request->ballot_id;
        $user=auth()->user();
        $is_voter =  $user->ballots()->where('ballot_id', $ballot_id)->first();
        $list =ListsElection::find($request->list_id);
        if(!empty($list) &&$is_voter){
            $voted =   $is_voter->pivot->voted;
            if( $voted){
                return  $this->returnErrorResponse('vote already casted');
            }
            $list->update(['count'=> DB::raw('count+1'),]);
            $user->ballots()->updateExistingPivot($ballot_id, ['voted'=>true]);
            return  $this->returnSuccessResponse('vote casted successfully');
           }

    }

    function results(Request $request){
        $request->merge(['id' => $request->route('id')]);
        $validation =  Validator::make($request->all(), [
            'id'=>'required|integer|exists:ballots,id',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
        $election= Ballot::where('id',$request->id)->first();

        $data["added_voters"] =$election->users()->where('ballot_id',$election->id )->count();
        $data["vote_casted"] =$election->users()->where(['ballot_id'=>$election->id ,
            'voted'=>true
        ])->count();
        $data["vote_ratio"] =  ( $data["added_voters"] == 0)?0: $data["vote_casted"]/  $data["added_voters"];
        $data["vote_ratio"] =(float) number_format((float) $data["vote_ratio"], 2, '.', '');

        $data["lists"]  =  ListsElection::where("election_id",$request->id)->orderBy('count', 'DESC')->with("candidates.candidate")->get();
        $data["lists"] ->each(function($list){
            $list->candidates->transform(function($candidate){
                $can=$candidate->candidate;
                return $can;

            });
            return $list;
        });

        $seats_number=ListsElection::find($election->id)->seats_number;
            $data["lists"]->transform(function($list)use(&$seats_number){
                if($seats_number>0){
                    $list->selected=true;
                    $seats_number--;
                }else{
                    $list->selected=false;
                }
                return $list;
            });
     return $this->returnDataResponse($data);


    }

    function lists(Request $request){
        $request->merge(['id' => $request->route('id')]);
        $validation =  Validator::make($request->all(), [
            'id'=>'required|integer|exists:ballots,id',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
        $data["lists"]  =  ListsElection::where("election_id",$request->id)->with("candidates.candidate")->get();
        $data["lists"] ->each(function($list){
            $list->candidates->transform(function($candidate){
                $can=$candidate->candidate;
               return $can;

            });
            return $list;
        });
        return  $this->returnDataResponse($data);

    }
    function update(UpdateElectionList $request){
        $jsonData= $request->get("body");
        if(!is_array($jsonData)) $jsonData= json_decode($request->get("body"),true);
        $is_valid=$request->is_valid($jsonData);
        if (!empty($is_valid)){
            return $is_valid;
        }
        try{
        $id= $jsonData['id'];
        $list=$request->list;
        if($request->hasFile('file')) {
            $response = cloudinary()->upload($request->file('file')->getRealPath(),[
                'folder'=> 'myballot/lists/',
                //'public_id'=>'picture'.$jsonData['id'],
               // 'overwrite'=>true,
                'format'=>"webp"
            ])->getSecurePath();
            $jsonData['picture']=$response;
        }
        unset($jsonData['list_id'],$jsonData['election_id']);
            $list->update($jsonData);
        return  $this->returnSuccessResponse('list updated successfully');
         }catch ( \Exception  $exception){
        return  $this->returnErrorResponse();
         }
    }

}
