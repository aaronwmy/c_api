<?php

namespace App\Rules\User;

use App\Models\User\School;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Rule;

class SchoolIsNotApproved implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //查询没有审核通过的学校信息
        $teacherInfo = School::where('user_id', $value)->where('status', '!=', School::APPROVED)->first();
        //查询用户个人信息
        $userInfo = User::where('id', $value)->where('type', User::USER_TYPE)->first();
        //如果没有审核通过的学校信息和用户个人信息都存在，则通过
        return !empty($teacherInfo) && !empty($userInfo);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.exists');
    }
}
