<?php

namespace App\Http\Requests;

use App\Helpers\AuthorizesAfterValidation;
use App\Helpers\MyHelper;
use App\Helpers\MyResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateQuestionRequest extends FormRequest
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
            'value' => 'string|min:10|max:400',
            'answers' => 'array',
            'answers.*.value' => 'required|string|min:4|max:255',
            'answers.*.id' => 'required|int|exists:answers,id',
            'type' => 'integer|min:1|max:2',
            "question_id"=>'required|integer|exists:questions,id',
            'ballot_id'=>'required|integer|exists:polls,id'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $res=   $this->returnValidationResponse($validator->errors());
        throw new HttpResponseException($res);
    }
}
