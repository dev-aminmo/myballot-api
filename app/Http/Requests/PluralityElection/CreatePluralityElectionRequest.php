<?php

namespace App\Http\Requests\PluralityElection;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreatePluralityElectionRequest extends FormRequest
{
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
            'start_date'    => 'required|date|date_format:Y-m-d H:i|after_or_equal:now',
            'end_date'      => 'required|date|date_format:Y-m-d H:i|after:start_date',
            'title'=> 'required|string|min:2|max:255',
            'description'=> 'string|min:10|max:400',
            'parties' => 'array|min:1|max:30',
            'parties.*.name'=>'required',
            'parties.*.candidates' => 'required|array|min:1|max:1',
            'parties.*.candidates.*.name' => 'required|string|min:4|max:255',
            'parties.*.candidates.*.description' => 'string|min:4|max:400',
            'free_candidates'=>'array|min:1|max:30',
            'free_candidates.*.name' => 'required|string|min:4|max:255',
            'free_candidates.*.description' => 'string|min:4|max:400',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $res= response()->json(["errors"=>$validator->errors(),
            "code"=>422
        ],422);
        throw new HttpResponseException($res);
    }
    public function validated()
    {
        return $this->validator->validated();
    }
}
