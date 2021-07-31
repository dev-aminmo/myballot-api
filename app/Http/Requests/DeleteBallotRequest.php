<?php

namespace App\Http\Requests;

use App\Helpers\MyHelper;
use App\Helpers\MyResponse;
use App\Models\Ballot;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Carbon;

class DeleteBallotRequest extends FormRequest
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
        $ballot_id=$this->route('id');
        return  (empty(Ballot::find($ballot_id))) ?  false: !$this->isStarted($ballot_id) && $this->isOrganizer($ballot_id);
    }

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
