<?php

namespace App\Http\Requests;

use App\Helpers\MyHelper;
use App\Helpers\MyResponse;
use App\Models\ListsElection\ListsElection;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddCandidateToLists extends FormRequest
{

    use MyResponse;
    use MyHelper;

    public $list;

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
            'body' => 'required|json',
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
        $res = $this->returnValidationResponse($validator->errors());
        throw new HttpResponseException($res);
    }

    public function is_valid($jsonData)
    {
        $validation = \Illuminate\Support\Facades\Validator::make($jsonData, [
            'name' => 'required|string|min:4|max:255',
            'description' => 'string|min:4|max:400',
            'list_id' => 'required|integer|exists:lists_elections,id'
        ]);
        if ($validation->fails()) {
            return $this->returnValidationResponse($validation->errors());
        }
        $this->list = ListsElection::find($jsonData["list_id"]);
        if (!$this->list) {
            return $this->returnValidationResponse(["Invalid list id"]);
        }
        if ($this->isStarted($this->list->election_id) || !$this->isOrganizer($this->list->election_id)) $this->failedAuthorization();

        return null;
    }
}
