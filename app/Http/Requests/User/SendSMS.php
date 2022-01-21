<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;

class SendSMS extends BaseFormRequest
{
    public function rules()
    {
        return [
            'mobile' => [
                'required',
                'regex:/^1[345789][0-9]{9}$/'
            ]
        ];
    }
}
