<?php

namespace App\Http\Controllers;

use App\Helpers\MyResponse;
use App\Http\Requests\AddPluralityPartyRequest;
use App\Http\Requests\DeletePartyRequest;
use App\Http\Requests\UpdateCandidatePartyRequest;
use App\Models\Candidate;
use App\Models\ListCandidate;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\MyHelper;

class PartyController extends Controller
{
    use MyHelper;
    use MyResponse;

    function add_to_plurality(AddPluralityPartyRequest $request){
        $election_id= $request->election_id;
        $party_id=Party::create(['name'=> $request->name, 'election_id'=> $election_id])->id;
        foreach( $request->candidates as $candidate){
           $candidate_id=Candidate::create([
                    'name'=> $candidate['name'],
                    'description'=>(!empty($candidate['description'])) ? $candidate['description'] : null,
                     'election_id'=>$election_id
                ]
            )->id;
            ListCandidate::create([
                    'id'=> $candidate_id,
                    'party_id'=>$party_id,
                ]
            );
        }
        return $this->returnSuccessResponse('party added successfully');

    }
    function update(UpdateCandidatePartyRequest $request){
        $jsonData= $request->get("body");
        if(!is_array($jsonData)) $jsonData= json_decode($request->get("body"),true);
        $is_valid=$request->is_valid_party($jsonData);
        if (!empty($is_valid)){
            return $is_valid;
        }
        try{
        $id= $jsonData['id'];
        $party=Party::where('id',$id)->first();
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
        return  $this->returnSuccessResponse('party updated successfully');
        }catch ( \Exception  $exception){
            return  $this->returnErrorResponse();
        }
    }
    function delete(DeletePartyRequest $request){
        $party=Party::where('id',$request->id)->first();
        $party->delete();
        return  $this->returnSuccessResponse('party deleted successfully');
    }

}
