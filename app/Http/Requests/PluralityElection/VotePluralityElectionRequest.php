<?php

namespace App\Http\Requests\PluralityElection;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\MyResponse;
use App\Helpers\MyHelper;


class VotePluralityElectionRequest extends FormRequest
{
    use MyResponse;
    use MyHelper;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->isStarted($this->election_id)&& !$this->isEnded($this->election_id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'election_id'=>'required|integer|exists:elections,id',
            'candidate_id'=>'required|integer|exists:candidates,id'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $res=   $this->returnValidationResponse($validator->errors());
        throw new HttpResponseException($res);
    }
}
