<?php

namespace App\Http\Controllers;

use App\Helpers\MyResponse;
use App\Http\Requests\AddFreeCandidateLists;
use App\Http\Requests\AddFreeCandidatesPlurality;
use App\Http\Requests\DeleteCandidateRequest;
use App\Http\Requests\UpdateCandidatePartyRequest;
use App\Models\Candidate;
use App\Models\Ballot;
use App\Models\PluralityCandidate;
use App\Models\ListCandidate;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\MyHelper;
class CandidateController extends Controller
{
    use MyHelper;
    use MyResponse;

    function delete(DeleteCandidateRequest $request,$id){
        try{
            $request->candidate->delete();
    //    Candidate::find($request->candidate_id)->delete();
        return $this->returnSuccessResponse('Candidate deleted successfully');
        }catch ( \Exception  $e){
            return  $this->returnErrorResponse("An error has occured");
        }
        }
    function update(UpdateCandidatePartyRequest $request){
        $jsonData= $request->get("body");

        if(!is_array($jsonData)) $jsonData= json_decode($request->get("body"),true);
        $is_valid=$request->is_valid_candidate($jsonData);
        if (!empty($is_valid)){
            return $is_valid;
        }
        try{
            $candidate=Candidate::where('id',$jsonData["id"])->first(); //with child
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
    //group
    function plurality_candidates(Request $request){
        $request->merge(['id' => $request->route('id')]);
        $validation =  Validator::make($request->all(), [
            'id'=>'required|integer|exists:ballots,id',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
     $type=Ballot::find($request->id)->type;
        switch ($type) {
            case 1:
                $data= PluralityCandidate::where('election_id',$request->id)->with("candidate")->get()->pluck("candidate");
                break;
            case 2:$data=Candidate::where('election_id',$request->id)->select('id','name','description','picture')->with(['partisan_candidate'=> function ($query) {
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
                break;
                default:$data=null;break;
        }
        return  $this->returnDataResponse($data);

    }
    function add_free_list(AddFreeCandidateLists $request){
        $list_id= $request->list_id;
        foreach( $request->candidates as $candidate){
            $candidate_id=Candidate::create([
                    'name'=> $candidate['name'],
                    'description'=>(!empty($candidate['description'])) ? $candidate['description'] : null,
                ]
            )->id;
            PluralityCandidate::create([
                    'id'=> $candidate_id,
                    "list_id"=>$list_id
                ]
            );
        }
        return $this->returnSuccessResponse('candidates added successfully');

    }
    function add_free_plurality(AddFreeCandidatesPlurality $request){
        $election_id= $request->election_id;
        foreach( $request->candidates as $candidate){
            $candidate_id=Candidate::create([
                    'name'=> $candidate['name'],
                    'description'=>(!empty($candidate['description'])) ? $candidate['description'] : null,
                    'type'=>1
                ]
            )->id;
            PluralityCandidate::create([
                    'id'=> $candidate_id,
                    'election_id'=>$election_id
                ]
            );
        }
        return $this->returnSuccessResponse('candidates added successfully');

    }



}
