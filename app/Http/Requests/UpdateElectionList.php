<?php

namespace App\Http\Requests;

use App\Helpers\AuthorizesAfterValidation;
use App\Helpers\MyHelper;
use App\Helpers\MyResponse;
use App\Models\ListsElection\FreeElectionList;
use App\Models\ListsElection\PartisanElectionList;
use App\Models\Party;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateElectionList extends FormRequest
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
    public function is_valid($jsonData){
        $validation = \Illuminate\Support\Facades\Validator::make($jsonData, [
           // 'id'=>'required|integer',
            'id' => 'required|integer',
            'name'=>'string|min:4|max:255',
            "program"=> 'string|min:4|max:255',
        ]);
        if ($validation->fails()) {
            return  $this->returnValidationResponse($validation->errors());
        }
        $list=FreeElectionList::find($jsonData["id"]);
        if(!$list){
            $list=   PartisanElectionList::find($jsonData["id"]);
            if(!$list){
                return  $this->returnValidationResponse(["Invalid list id"]);
            }
            }
        if ($this->isStarted($list->election_id) || !$this->isOrganizer($list->election_id))$this->failedAuthorization();

        return null;
    }
}
