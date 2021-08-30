<?php

namespace App\Http\Requests;

use App\Helpers\AuthorizesAfterValidation;
use App\Helpers\MyHelper;
use App\Helpers\MyResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddListRequest extends FormRequest
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
            'file' => 'mimes:jpg,jpeg,png,bmp|max:20000',
            'body'=>'required|json',
        ];
    }
    public function messages()
    {
        return [
            'file.required' => 'Please upload an image',
            'file.mimes' => 'Only jpeg,jpg,png and bmp images are allowed',
            'file.max' => 'Sorry! Maximum allowed size for an image is 20MB',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $res=   $this->returnValidationResponse($validator->errors());
        throw new HttpResponseException($res);
    }
    public function is_valid($jsonData){
        $validation = \Illuminate\Support\Facades\Validator::make($jsonData, [
            'name'=>'required|string|min:2|max:255',
            'program'=>'string|string|min:2|max:400',
            'candidates' => 'array|max:30',
            'candidates.*.name' => 'required|string|min:4|max:255',
            'candidates.*.description' => 'string|min:4|max:400',
            'ballot_id'=>'required|integer|exists:ballots,id'
        ]);
        if ($validation->fails()) {
            return  $this->returnValidationResponse($validation->errors());
        }
        if ($this->isStarted($jsonData["ballot_id"]) || !$this->isOrganizer($jsonData["ballot_id"]))  $this->failedAuthorization();

        return null;
    }

}
