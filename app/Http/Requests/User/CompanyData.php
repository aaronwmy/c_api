<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;
use App\Rules\File\FileExists;
use Illuminate\Support\Facades\Storage;

class CompanyData extends BaseFormRequest
{
    public function rules()
    {
        return [
            'business_license_code' => [
                'regex:/(^(?:(?![IOZSV])[\dA-Z]){2}\d{6}(?:(?![IOZSV])[\dA-Z]){10}$)|(^\d{15}$)/'
            ],
            'id_card_number' => [
                'regex:/^[1-9]\d{14}(\d{2}[0-9X])?$/'
            ],
            'business_license_pic' => [
                new FileExists()
            ],
            'company_cover' => [
                new FileExists()
            ],
            'region_code' => [
                'exists:regions,region_code,level,3'
            ]
        ];
    }
}
