<?php

namespace App\Http\Requests;

use App\Helpers\MyHelper;
use App\Helpers\MyResponse;
use App\Models\Candidate;
use App\Models\Party;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCandidatePartyRequest extends FormRequest
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
            'body'=>'required',
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
    public function is_valid_candidate($jsonData){
        $validation = \Illuminate\Support\Facades\Validator::make($jsonData, [
            'id'=>'required|integer|exists:candidates,id',
            'name'=>'string|min:4|max:255',
            'description' => 'string|min:4|max:400',
        ]);
        if ($validation->fails()) {
            return  $this->returnValidationResponse($validation->errors());
        }
        $candidate=Candidate::where('id',$jsonData["id"])->first();
        if ($this->isStarted($candidate->election_id) || !$this->isOrganizer($candidate->election_id)) $this->failedAuthorization();
    }

        public function is_valid_party($jsonData){
        $validation = \Illuminate\Support\Facades\Validator::make($jsonData, [
            'id'=>'required|integer|exists:parties,id',
            'name'=>'string|min:4|max:255',
        ]);
        if ($validation->fails()) {
            return  $this->returnValidationResponse($validation->errors());
        }
        $party=Party::where('id',$jsonData["id"])->first();
        if ($this->isStarted($party->election_id) || !$this->isOrganizer($party->election_id))$this->failedAuthorization();

        return null;
    }

}
