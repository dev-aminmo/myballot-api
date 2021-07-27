<?php

namespace App\Http\Requests;

use App\Helpers\AuthorizesAfterValidation;
use App\Helpers\MyHelper;
use App\Helpers\MyResponse;
use App\Models\ListsElection\ElectionList;
use App\Models\ListsElection\ListsElection;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddCandidateToLists extends FormRequest
{

    use MyResponse;
    use MyHelper;
    use AuthorizesAfterValidation;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorizeValidated()
    {
      $list=  ListsElection::find($this->list_id);
        return !$this->isStarted($list->election_id) && $this->isOrganizer($list->election_id);
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'candidates' => 'required|array|min:1',
            'candidates.*.name' => 'required|string|min:4|max:255',
            'candidates.*.description' => 'string|min:4|max:400',
            'list_id'=>'required|integer|exists:lists_elections,id'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $res=   $this->returnValidationResponse($validator->errors());
        throw new HttpResponseException($res);
    }
}
