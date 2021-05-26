<?php

namespace App\Http\Requests;

use App\Helpers\MyHelper;
use App\Helpers\MyResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddVotersRequest extends FormRequest
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
        if($this->election_id)  return !$this->isStarted($this->election_id) && $this->isOrganizer($this->election_id);
    return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'election_id' => 'required|integer|exists:elections,id',
            'emails' => 'required|array|min:1|max:150',
            'emails.*' => 'email',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $res=   $this->returnValidationResponse($validator->errors());
        throw new HttpResponseException($res);
    }
}
