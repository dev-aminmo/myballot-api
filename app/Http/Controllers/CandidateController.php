<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\PartisanCandidate;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\MyHelper;
class CandidateController extends Controller
{
    use MyHelper;
    //
    function update_partisan(Request $request){
        $validation =  Validator::make($request->all(), [
            'id'=>'required|integer|exists:partisan_candidates,id',
            'name' => 'string|min:4|max:255',
            'description' => 'string|min:4|max:400',
        ]);
        if ($validation->fails()){
            return response()->json($validation->errors(), 422);
        }
        try{
            $id= $request->id;
            $candidate=Candidate::where('id',$id)->first();
           // $party=Party::where('id',$candidate->id)->first();
            if ($this->isStarted($candidate->election_id) || !$this->isOrganizer($candidate->election_id)) return  redirect('/');
            $candidate->update($request->only(['name', 'description']));
            $data = ['message' => 'candidate updated successfully','code'=>201];
            return Response()->json($data,201);
        }catch ( \Exception  $exception){
            $response=['message'=>"error has occurred",
                "code"=>"400"];
            return response()->json($response, 400);
        }
    }

}
