<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;
use App\Rules\File\FileExists;

class SchoolData extends BaseFormRequest
{
    public function rules()
    {
        return [
            'enterprise_legal_person_certificate_pic' => [
                new FileExists()
            ],
            'school_cover' => [
                new FileExists()
            ],
            'region_code' => [
                'exists:regions,region_code,level,3'
            ]
        ];
    }
}
