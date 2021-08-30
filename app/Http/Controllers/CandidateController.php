<?php

namespace App\Http\Controllers;

use App\Helpers\MyResponse;
use App\Http\Requests\AddCandidateToLists;
use App\Http\Requests\AddCandidatesToPlurality;
use App\Http\Requests\DeleteCandidateRequest;
use App\Http\Requests\UpdateCandidateRequest;
use App\Models\Candidate;
use App\Models\Ballot;
use App\Models\ListsElection\ListsElection;
use App\Models\PluralityCandidate;
use App\Models\ListCandidate;
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
    function update(UpdateCandidateRequest $request){
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
       // dd($type);
        switch ($type) {
            case "plurality":
                $data["candidates"]= PluralityCandidate::where('election_id',$request->id)->with("candidate")->get()->pluck("candidate");
                break;
            case "lists":
            $isis  =  ListsElection::where("election_id",$request->id)->with("candidates.candidate")->get();
                $isis ->each(function($list){
                    $list->candidates->transform(function($candidate){
                        $can=$candidate->candidate;
                        return $can;
                    });
                    return $list;
                });
                $data["candidates"]=$isis->pluck("candidates");

                break;
                default:$data=null;break;
        }
        return  $this->returnDataResponse($data);

    }
    function add_candidates_to_list(AddCandidateToLists $request){
        $jsonData= $request->get("body");
        if(!is_array($jsonData)) $jsonData= json_decode($request->get("body"),true);
        $is_valid=$request->is_valid($jsonData);
        if (!empty($is_valid)){
            return $is_valid;
        }
        try{
        $list_id=  $jsonData['list_id'];

            $candidate=Candidate::create([
                    'name'=> $jsonData['name'],
                    'description'=>(!empty($jsonData['description'])) ? $jsonData['description'] : null,
                    "type"=>2
                ]
            );
            ListCandidate::create([
                    'id'=> $candidate->id,
                    "list_id"=>$list_id
                ]);
            if($request->hasFile('file')) {
                $response = cloudinary()->upload($request->file('file')->getRealPath(),[
                    'folder'=> 'myballot/candidates/',
                    'public_id'=>'picture'.$candidate->id,
                    'overwrite'=>true,
                    'format'=>"webp"
                ])->getSecurePath();
                $candidate->picture=$response;
                $candidate->save();
            }

        return $this->returnSuccessResponse('candidates added successfully'); }
        catch ( \Exception  $exception){
            return  $this->returnErrorResponse();
        }

    }
    function add_candidates_to_plurality(AddCandidatesToPlurality $request){
        $jsonData= $request->get("body");
        if(!is_array($jsonData)) $jsonData= json_decode($request->get("body"),true);
        $is_valid=$request->is_valid($jsonData);
        if (!empty($is_valid)){
            return $is_valid;
        }
        try{
        $election_id= $jsonData['election_id'];
            $candidate=Candidate::create([
                    'name'=> $jsonData['name'],
                    'description'=>(!empty($jsonData['description'])) ? $jsonData['description'] : null,
                    'type'=>1
                ]
            );
            PluralityCandidate::create([
                    'id'=> $candidate->id,
                    'election_id'=>$election_id
                ]
            );
            if($request->hasFile('file')) {
                $response = cloudinary()->upload($request->file('file')->getRealPath(),[
                    'folder'=> 'myballot/candidates/',
                    'public_id'=>'picture'.$candidate->id,
                    'overwrite'=>true,
                    'format'=>"webp"
                ])->getSecurePath();
                $candidate->picture=$response;
                $candidate->save();
            }
        return $this->returnSuccessResponse('candidates added successfully'); }
        catch ( \Exception  $exception){
            return  $this->returnErrorResponse();
        }

        }


/*    function add_candidates_to_plurality(AddCandidatesToPlurality $request){
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

    }*/



}
