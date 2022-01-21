<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;

class UserWithdrawal extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'exists:user_withdrawals,id'
            ]
        ];
    }
}
