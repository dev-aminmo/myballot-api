<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\PartisanCandidate;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\MyHelper;
class CandidateController extends Controller
{
    use MyHelper;

    function update(Request $request){
        $validation = Validator::make(
            $request->all(), [
            'body'=>'required',
            'file.*' => 'required|mimes:jpg,jpeg,png,bmp|max:10000',
        ],[
                'file.*.required' => 'Please upload an image',
                'file.*.mimes' => 'Only jpeg,png and bmp images are allowed',
                'file.*.max' => 'Sorry! Maximum allowed size for an image is 10MB',
            ]
        );
        if($validation->fails()) {
            return response()->json($validation->errors(), 202);
        }
        $jsonData=$request->get("body");
        if(!is_array($jsonData)) $jsonData= json_decode($request->get("body"),true);
        $validation = Validator::make($jsonData, [
            'id'=>'required|integer|exists:candidates,id',
            'name'=>'string|min:4|max:255',
            'description' => 'string|min:4|max:400',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 202);
        }
        try{
            $id= $jsonData['id'];
            $candidate=Candidate::where('id',$id)->first(); //with child

            if ($this->isStarted($candidate->election_id) || !$this->isOrganizer($candidate->election_id)) return  redirect('/');

            if($request->hasFile('file')) {
                $response = cloudinary()->upload($request->file('file')->getRealPath(),[
                    'folder'=> 'myballot/candidates/',
                    'public_id'=>'picture'.$jsonData['id'],
                    'overwrite'=>true,
                    'format'=>"webp"
                ])->getSecurePath();
                $jsonData['picture']=$response;
            }
            unset($jsonData['count'],$jsonData['election_id']);
            $candidate->update($jsonData);
            $data = ['message' => 'candidate updated successfully','code'=>201];
            return Response()->json($data,201);
        }catch ( \Exception  $exception){
            return response()->json(["error"=>"An error has occured","code"=>400], 400);
        }
    }
    function plurality_candidates(Request $request){
        $request->merge(['id' => $request->route('id')]);
        $validation =  Validator::make($request->all(), [
            'id'=>'required|integer|exists:elections,id',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
        $data["data"]=Candidate::where('election_id',$request->id)->select('id','name','description','picture')->with(['partisan_candidate'=> function ($query) {
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
        $data["code"]=200;
        return Response()->json($data,200);
    }



}
