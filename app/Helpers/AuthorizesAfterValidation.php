<?php

namespace App\Helpers;

trait AuthorizesAfterValidation
{
    public function authorize()
    {
        return true;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (! $validator->failed() && ! $this->authorizeValidated()) {
                $this->failedAuthorization();
            }
        });
    }

    abstract public function authorizeValidated();
}
