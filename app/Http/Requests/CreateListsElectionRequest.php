<?php

namespace App\Http\Requests;

use App\Helpers\MyResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Carbon;

class CreateListsElectionRequest extends FormRequest
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
            'type'=>'required|integer|min:0|max:1',
            'seats_number'=>'required|integer|min:1',
            'partisan_lists' => 'array|min:1|max:30',
            'partisan_lists.*.name'=>'required',
            'partisan_lists.*.program'=>'string|string|min:2|max:400',
            'partisan_lists.*.parties' => 'required|array|max:1',
            'partisan_lists.*.parties.*.candidates.*.name' => 'required|string|min:4|max:255',
            'partisan_lists.*.parties.*.candidates.*.description' => 'string|min:4|max:400',
            'free_lists' => 'array|min:1|max:30',
            'free_lists.*.name'=>'required|string|min:2|max:255',
            'free_lists.*.program'=>'string|string|min:2|max:400',
            'free_lists.*.candidates' => 'required|array|max:30',
            'free_lists.*.candidates.*.name' => 'required|string|min:4|max:255',
            'free_lists.*.candidates.*.description' => 'string|min:4|max:400',
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

        $lists_count=0;
        if (!empty($this->free_lists)) {
            $lists_count +=count($this->free_lists);
        }
        if (!empty($this->partisan_lists)) {
            $lists_count +=count($this->partisan_lists);
        }
        if ($lists_count<2) {
            throw new HttpResponseException( $this->returnValidationResponse(["the minimum number of lists is 2"]));
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
