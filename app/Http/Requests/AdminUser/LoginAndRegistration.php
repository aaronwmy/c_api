<?php

namespace App\Http\Requests\AdminUser;

use App\Http\Requests\BaseFormRequest;

class LoginAndRegistration extends BaseFormRequest
{
    public function rules()
    {
        return [
            'username' => [
                'required'
            ],
            'password' => [
                'required'
            ]
        ];
    }
}
