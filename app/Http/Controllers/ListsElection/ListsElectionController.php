<?php

namespace App\Http\Controllers\ListsElection;

use App\Http\Controllers\Controller;
use App\Models\FreeCandidate;
use App\Models\ListsElection\FreeElectionList;
use App\Models\ListsElection\ListsElection;
use App\Models\ListsElection\PartisanElectionList;
use App\Models\PartisanCandidate;
use App\Models\Party;
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

    function create(Request $request){
        $validation =  Validator::make($request->all(), [
            'start_date'    => 'required|date|date_format:Y-m-d H:i|after_or_equal:now',
            'end_date'      => 'required|date|date_format:Y-m-d H:i|after:start_date',
            'title'=> 'required|string|min:2|max:255',
            'description'=> 'string|min:10|max:400',
            'partisan_lists' => 'array|min:1|max:30',
            'partisan_lists.*.name'=>'required',
            'partisan_lists.*.program'=>'string|string|min:2|max:400',
            'partisan_lists.*.parties' => 'required|array|max:1',
            'partisan_lists.*.parties.*.candidates.*.name' => 'required|string|min:4|max:255',
            'partisan_lists.*.parties.*.candidates.*.description' => 'string|min:4|max:400',
            'free_lists' => 'array|min:1|max:30',
            'free_lists.*.name'=>'required|string|min:2|max:255',
            'free_lists.*.program'=>'string|string|min:2|max:400',
            'free_lists.*.candidates' => 'required|array|max:30',
            'free_lists.*.candidates.*.name' => 'required|string|min:4|max:255',
            'free_lists.*.candidates.*.description' => 'string|min:4|max:400',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
        $lists_count=0;
        if (!empty($request->free_lists)) {
            $lists_count +=count($request->free_lists);
        }
        if (!empty($request->partisan_lists)) {
            $lists_count +=count($request->partisan_lists);
        }
        if ($lists_count<2) {
            return response()->json([
                'message'=>"the minimum number of candidates is 2",
                'code'=>'422'] ,422);
        }
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
            $id= auth()->user()['id'];
            $allData = $request->all();
            $allData['organizer_id']=$id;

            $election_id=ListsElection::create($allData)->id;
           // die;

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
                    foreach($partisan_list["parties"] as $party){
                    $party_id = Party::create(['name'=> $party['name'],
                        'list_id'=>$list_id,])->id;

                        foreach($party['candidates']as $candidate){
                        PartisanCandidate::create([
                                'name'=> $candidate['name'],
                                'description'=>(!empty($candidate['description'])) ? $candidate['description'] : null,
                                'party_id'=>$party_id,
                                'election_id'=>$election_id,
                                'list_id'=>$list_id
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

                    FreeCandidate::create([
                            'name'=> $candidate['name'],
                            'description'=>(!empty($candidate['description'])) ? $candidate['description'] : null,
                            'list_id'=>$list_id
                        ]
                    );
                }
                }
            }
            $data = ['message' => 'election created successfully','code'=>201];
            return Response()->json($data,201);
        }catch ( \Exception  $exception){
            $response['error']=$exception;
            return response()->json($exception->getTrace(), 400);
        }
    }

}
