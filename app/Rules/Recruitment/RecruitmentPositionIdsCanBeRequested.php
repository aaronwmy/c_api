<?php

namespace App\Rules\Recruitment;

use App\Models\Recruitment\RecruitmentPosition;
use App\Models\Recruitment\RecruitmentPositionRequest;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Rule;

class RecruitmentPositionIdsCanBeRequested implements Rule
{
    private $msg;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
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
        $ids = explode(',', $value);
        for ($i = 0; $i < count($ids); $i++) {
            //判断申请的职位是否存在
            if (RecruitmentPosition::where('id', $ids[$i])->count() == 0) {
                $this->msg = __('validation.exists');
                return false;
            }
            //判断这个职位是否已经申请过了
            if (RecruitmentPositionRequest::where('position_id', $ids[$i])->where('user_id', User::getCurrentUserCache('id'))->count() > 0) {
                $this->msg = __('validation.unique');
                return false;
            }
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->msg;
    }
}
