<?php

namespace App\Http\Controllers;

use App\Helpers\MyResponse;
use App\Http\Requests\UpdateCandidateRequest;
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
    use MyResponse;


    function update(UpdateCandidateRequest $request){
        $jsonData=$request->get("body");
        if(!is_array($jsonData)) $jsonData= json_decode($request->get("body"),true);
        $is_valid=$request->is_valid($jsonData);
        if (!empty($is_valid)){
            return $is_valid;
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
            return  $this->returnSuccessResponse('candidate updated successfully');
        }catch ( \Exception  $exception){
            return  $this->returnErrorResponse();

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
        $data=Candidate::where('election_id',$request->id)->select('id','name','description','picture')->with(['partisan_candidate'=> function ($query) {
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
        return  $this->returnDataResponse($data);

    }



}
