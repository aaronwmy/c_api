<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;
use App\Models\User\User;
use Illuminate\Validation\Rule;

class UserBank extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'exists:user_banks,id,user_id,' . User::getCurrentUserCache('id')
            ],
            'bank_id' => [
                'exists:banks,id'
            ],
            'bank_card_number' => [
                'regex:/^([1-9]{1})(\d{15}|\d{18})$/'
            ],
            'region_code' => [
                Rule::exists('regions', 'region_code')->whereIn('level', [2, 3])
            ]
        ];
    }
}
