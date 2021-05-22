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
/*        $validation =  Validator::make($request->all(), [
            'id'=>'required|integer|exists:parties,id',
            'name'=>'required|string|min:4|max:255',
        ]);*/
        $validation = Validator::make(
            $request->all(), [
            'body'=>'required',
            //'file'=>'required',
            'file.*' => 'required|mimes:jpg,jpeg,png,bmp|max:20000',
        ],[
                'file.*.required' => 'Please upload an image',
                'file.*.mimes' => 'Only jpeg,png and bmp images are allowed',
                'file.*.max' => 'Sorry! Maximum allowed size for an image is 20MB',
            ]
        );
        if($validation->fails()) {
            return response()->json($validation->errors(), 202);
        }
        $jsonData=$request->get("body");
        if(!is_array($jsonData)) $jsonData= json_decode($request->get("body"),true);
        $validation = Validator::make($jsonData, [
            'id'=>'required|integer|exists:parties,id',
            'name'=>'string|min:4|max:255',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 202);
        }
        try{
        $id= $jsonData['id'];
        $party=Party::where('id',$id)->first();
        if ($this->isStarted($party->election_id) || !$this->isOrganizer($party->election_id)) return  redirect('/');

        if($request->hasFile('file')) {
            $response = cloudinary()->upload($request->file('file')->getRealPath(),[
                'folder'=> 'myballot/parties/',
                'public_id'=>'picture'.$jsonData['id'],
                'overwrite'=>true,
                'format'=>"webp"
            ])->getSecurePath();
            $jsonData['picture']=$response;
        }
        unset($jsonData['list_id'],$jsonData['election_id']);
        $party->update($jsonData);
        $data = ['message' => 'party updated successfully','code'=>201];
        return Response()->json($data,201);
        }catch ( \Exception  $exception){
            return response()->json(["error"=>"An error has occured","code"=>400], 400);
        }
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
