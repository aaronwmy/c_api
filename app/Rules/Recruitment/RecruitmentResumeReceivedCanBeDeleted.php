<?php

namespace App\Rules\Recruitment;

use App\Models\Recruitment\RecruitmentPosition;
use App\Models\Recruitment\RecruitmentPositionRequest;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Rule;

class RecruitmentResumeReceivedCanBeDeleted implements Rule
{
    private $dataOperator;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($dataOperator)
    {
        $this->dataOperator = $dataOperator;
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
        //查询收到的简历
        $resumeInfo = RecruitmentPositionRequest::leftJoin(
            'recruitment_positions',
            'recruitment_position_requests.position_id',
            'recruitment_positions.id'
        )->where(
            'recruitment_position_requests.resume_id',
            $value
        )->where(
            'recruitment_positions.user_id',
            User::getCurrentUserCache('id')
        )->where(
            'recruitment_position_requests.status',
            RecruitmentPositionRequest::DELIVERED
        )->select('recruitment_position_requests.id')->first();
        $this->dataOperator->setResumeInfo($resumeInfo);
        return !empty($resumeInfo);
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
