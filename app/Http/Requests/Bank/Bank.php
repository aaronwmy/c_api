<?php

namespace App\Http\Requests\Bank;

use App\Http\Requests\BaseFormRequest;
use App\Rules\File\FileExists;

class Bank extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'exists:banks,id'
            ],
            'bank_logo' => [
                new FileExists()
            ]
        ];
    }
}
