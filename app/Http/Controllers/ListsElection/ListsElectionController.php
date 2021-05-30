<?php

namespace App\Http\Controllers\ListsElection;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateListsElectionRequest;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\FreeCandidate;
use App\Models\ListsElection\FreeElectionList;
use App\Models\ListsElection\ListsElection;
use App\Models\ListsElection\PartisanElectionList;
use App\Models\PartisanCandidate;
use App\Models\Party;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class ListsElectionController extends Controller
{
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
            $election_id=Election::create($allData)->id;
            ListsElection::create([
                'id'=>$election_id
            ]);
            if (!empty($request->partisan_lists)) {
                foreach($request->partisan_lists as $partisan_list){
                   $name=$partisan_list["name"];
                   $program=$partisan_list["program"];
                 $list_id=PartisanElectionList::create(
                     [
                      "name"=>$name,
                      "program"=>$program,
                      "election_id"=>$election_id
                     ]
                 )->id;

                  // dd( $list_id);
                    foreach($partisan_list["parties"] as $party){
                    $party_id = Party::create(['name'=> $party['name'],
                        'list_id'=>$list_id,])->id;

                        foreach($party['candidates']as $candidate){
                          //  dd($candidate['name']);
                           $candidate_id= Candidate::create([
                               'name'=> $candidate['name'],
                               'description'=>(!empty($candidate['description'])) ? $candidate['description'] : null,
                           //    'election_id'=>$election_id,
                           ])->id;
                        PartisanCandidate::create([
                                'id'=>$candidate_id,
                                'party_id'=>$party_id,
                            ]);
                    }
                }}
            }
            if (!empty($request->free_lists)) {
                foreach($request->free_lists as $free_list){
                   $list_id= FreeElectionList::create([
                       "name"=>$free_list["name"],
                       "program"=>$free_list["program"],
                       "election_id"=>$election_id
                   ])->id;
                foreach($free_list["candidates"] as $candidate){
                    $candidate_id= Candidate::create([
                        'name'=> $candidate['name'],
                        'description'=>(!empty($candidate['description'])) ? $candidate['description'] : null,
                        //'election_id'=>$election_id,
                    ])->id;
                    FreeCandidate::create([
                        'id'=>$candidate_id,
                            'list_id'=>$list_id
                        ]
                    );
                }
                }
            }
            $data = ['message' => 'election created successfully','code'=>201];
            return Response()->json($data,201);
        }catch ( \Exception  $exception){
            throw
            $response['error']=$exception;
            return response()->json($exception->getTrace(), 400);
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
        //TODO number of chairs
        $election= Election::where('id',$request->id)->first();
        $data["added_voters"] =$election->users()->where('election_id',$election->id )->count();
        $initial_vote_casted =$election->users()->where(['election_id'=>$election->id ,
            'voted'=>true
        ])->count();
        $data["vote_ratio"] =  ( $data["added_voters"] == 0)?0: $data["vote_casted"]/  $data["added_voters"];
        $data["vote_ratio"] =(float) number_format((float) $data["vote_ratio"], 2, '.', '');

        return "hello";

    }



}
