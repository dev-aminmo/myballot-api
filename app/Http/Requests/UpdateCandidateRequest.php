<?php

namespace App\Http\Requests;

use App\Helpers\MyHelper;
use App\Helpers\MyResponse;
use App\Models\Candidate;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCandidateRequest extends FormRequest
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

        switch($candidate->type){
            case 1:
                $election_id=$candidate->plurality_candidate->election_id;break;
            case 2:
                $election_id=$candidate->list_candidate->listx->election_id;break;
        }
        if ($this->isStarted($election_id) || !$this->isOrganizer($election_id)) $this->failedAuthorization();
      //  return true;
    }


}
