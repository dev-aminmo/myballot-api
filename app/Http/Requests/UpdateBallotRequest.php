<?php

namespace App\Http\Requests;

use App\Helpers\MyHelper;
use App\Helpers\MyResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Carbon;

class UpdateBallotRequest extends FormRequest
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
            'ballot_id'=>'required|integer|exists:ballots,id',
            'start_date'    => 'required|date|date_format:Y-m-d H:i|after_or_equal:now',
            'end_date'      => 'required|date|date_format:Y-m-d H:i|after:start_date',
            'title'=> 'string|min:2|max:255',
            'description'=> 'string|min:10|max:400',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $res=   $this->returnValidationResponse($validator->errors());
        throw new HttpResponseException($res);
    }

    /**
     * this method check that the difference
     * between start_date and end_date is more than 5 minutes
     */
    public function is_valid()
    {
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
                if (!$this->authorizeValidated()) {
                    $this->failedAuthorization();
                }
            }
        });
    }
}
