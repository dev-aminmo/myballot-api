<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\PartisanCandidate;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\MyHelper;

class PartyController extends Controller
{
    use MyHelper;
    function add_to_plurality(Request $request){
        $validation =  Validator::make($request->all(), [
            'name'=>'required',
            'candidates' => 'required|array|min:1|max:1',
            'candidates.*.name' => 'required|string|min:4|max:255',
            'candidates.*.description' => 'string|min:4|max:400',
            'election_id'=>'required|integer|exists:plurality_elections,id'
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
        $election_id= $request->election_id;
        if ($this->isStarted($election_id) || !$this->isOrganizer($election_id)) return  redirect('/');
        $party_id=Party::create(['name'=> $request->name,
            'election_id'=> $election_id])->id;
        foreach( $request->candidates as $candidate){

           $candidate_id=Candidate::create([
                    'name'=> $candidate['name'],
                    'description'=>(!empty($candidate['description'])) ? $candidate['description'] : null,
                     'election_id'=>$election_id
                ]
            )->id;
            PartisanCandidate::create([
                    'id'=> $candidate_id,
                    'party_id'=>$party_id,
                ]
            );
        }
        $data = ['message' => 'party added successfully','code'=>201];
        return Response()->json($data,201);
    }
    function update(Request $request){
        $validation =  Validator::make($request->all(), [
            'id'=>'required|integer|exists:parties,id',
            'name'=>'required|string|min:4|max:255',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
        $id= $request->id;
        $party=Party::where('id',$id)->first();
        if ($this->isStarted($party->election_id) || !$this->isOrganizer($party->election_id)) return  redirect('/');
        $party->name=$request->name;
        $party->save();
        $data = ['message' => 'party updated successfully','code'=>201];
        return Response()->json($data,201);
    }
    function delete(Request $request){
        $validation =  Validator::make($request->all(), [
            'id'=>'required|integer|exists:parties,id']);
        if ($validation->fails()){
            return response()->json($validation->errors(), 422);
        }
        $id= $request->id;
        $party=Party::where('id',$id)->first();
        if ($this->isStarted($party->election_id) ||!$this->isOrganizer($party->election_id)) return  redirect('/');
        $party->delete();
        $data = ['message' => 'party deleted successfully','code'=>201];
        return Response()->json($data,201);
    }

}
