<?php

namespace App\Http\Requests;

use App\Helpers\AuthorizesAfterValidation;
use App\Helpers\MyHelper;
use App\Helpers\MyResponse;
use App\Models\ListsElection\ListsElection;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DeleteListRequest extends FormRequest
{
    use MyResponse;
    use MyHelper;
    use AuthorizesAfterValidation;
    public $list;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorizeValidated()
    {

        $list_id=$this->route('id');
        $this->list=ListsElection::find($list_id);
        if($this->list){
            $election_id= $this->list->election_id;
            return !$this->isStarted($election_id) && $this->isOrganizer($election_id);
        }else{
            return false;
        }
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
