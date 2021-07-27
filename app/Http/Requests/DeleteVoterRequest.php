<?php

namespace App\Http\Requests;

use App\Helpers\AuthorizesAfterValidation;
use App\Helpers\MyHelper;
use App\Helpers\MyResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DeleteVoterRequest extends FormRequest
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
        return !$this->isStarted($this->ballot_id) && $this->isOrganizer($this->ballot_id);
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ballot_id' => 'required|integer|exists:ballots,id',
            'voter_id' => 'required|integer|exists:users,id',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $res=   $this->returnValidationResponse($validator->errors());
        throw new HttpResponseException($res);
    }
}
