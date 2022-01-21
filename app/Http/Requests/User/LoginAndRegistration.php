<?php

namespace App\Http\Requests\User;

use App\Common\Constant;
use App\Http\Requests\BaseFormRequest;
use App\Models\User\MobileCode;

class LoginAndRegistration extends BaseFormRequest
{
    public function rules()
    {
        $mobile = (string)$this->input('mobile', '');
        return [
            'mobile' => [
                'required',
                'regex:/^1[345789][0-9]{9}$/'
            ],
            'verification_code' => [
                'required',
                'regex:/^[0-9]{6}$/',
                function ($attribute, $value, $fail) use ($mobile) {
                    if (MobileCode::where('mobile', $mobile)->where('code', $value)->where('created_at', '>=', date('Y-m-d H:i:s', time() - Constant::EFFECTIVE_MINUTES_OF_SMS * 60))->count() == 0) {
                        return $fail(__('validation.exists'));
                    }
                }
            ]
        ];
    }
}
