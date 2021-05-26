<?php

namespace App\Http\Requests\PluralityElection;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\MyResponse;
use Illuminate\Support\Carbon;


class CreatePluralityElectionRequest extends FormRequest
{
    use MyResponse;

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
        $res=   $this->returnValidationResponse($validator->errors());
        throw new HttpResponseException($res);
    }
    public function validated()
    {
        return $this->validator->validated();
    }

    /**
     * this method check that there is more than two candidates and the difference
     * between start_date and end_date is more than 5 minutes
     */
    public function is_valid()
    {

        $candidates_count=0;
        if (!empty($this->free_candidates)) {
            $candidates_count +=count($this->free_candidates);
        }
        if (!empty($this->parties)) {
            foreach($this->parties as $party){
                $candidates_count += count(  $party['candidates']);
            }
        }
        if ($candidates_count<2) {
            throw new HttpResponseException( $this->returnValidationResponse(["the minimum number of candidates is 2"]));
        }

        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);
        $diff_in_minutes = $end->diffInMinutes($start);
        if ($diff_in_minutes < 5)  {
       throw new HttpResponseException( $this->returnValidationResponse(["the difference between start_date and end_date should be more than 5 minutes"]));
        }
    }
    /**
     * calling is_valid() after request validation
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (! $validator->failed() ) {
                $this->is_valid();
            }
        });
    }
}
