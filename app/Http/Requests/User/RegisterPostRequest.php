<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterPostRequest extends FormRequest
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
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'name' => 'required|string'

        ];
    }
    public function messages()
    {
        return [
            'email.required' => 'Email is required!',
            'password.required' => 'Password is required!',
            'name.required' => 'Email is required!'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $res= response()->json(["errors"=>$validator->errors(),
         "code"=>422],422);
        throw new HttpResponseException($res);
    }
    public function getAttributes(){
        return array_merge($this->only(['name','email']),["password"=>bcrypt($this->password)]);
    }
}
