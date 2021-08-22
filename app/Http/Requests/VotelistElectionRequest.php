<?php

namespace App\Http\Requests\PluralityElection;

use App\Helpers\AuthorizesAfterValidation;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\MyResponse;
use App\Helpers\MyHelper;


class VotelistElectionRequest extends FormRequest
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
      return $this->isStarted($this->ballot_id)&& !$this->isEnded($this->ballot_id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'ballot_id'=>'required|integer|exists:ballots,id',
            'list_id'=>'required|integer|exists:lists_elections,id'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $res=   $this->returnValidationResponse($validator->errors());
        throw new HttpResponseException($res);
    }
}
