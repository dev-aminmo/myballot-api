<?php

namespace App\Http\Requests;

use App\Helpers\AuthorizesAfterValidation;
use App\Helpers\MyHelper;
use App\Helpers\MyResponse;
use App\Models\Candidate;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DeleteCandidateRequest extends FormRequest
{
    use MyResponse;
    use MyHelper;
    use AuthorizesAfterValidation;
    public $candidate;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorizeValidated()
    {
      $candidate_id=$this->route('id');
        $this->candidate=Candidate::find($candidate_id);

if($this->candidate){
        switch($this->candidate->type){
            case 1:
                $election_id=$this->candidate->plurality_candidate->election_id;
                ;break;
            //TODO ListCandidate
            case 2:;break;
        }
        return !$this->isStarted($election_id) && $this->isOrganizer($election_id);
    }else{
        return false;
    }}
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
      ];
    }

    protected function failedValidation(Validator $validator)
    {
        $res=   $this->returnValidationResponse($validator->errors());
        throw new HttpResponseException($res);
    }
}
