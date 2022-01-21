<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;
use App\Rules\File\FileExists;

class UserData extends BaseFormRequest
{
    public function rules()
    {
        return [
            'portrait' => [
                new FileExists()
            ],
            'sex' => [
                'in:1,2'
            ],
            'type' => [
                'in:1,2,3,4'
            ]
        ];
    }
}
