<?php

namespace App\Rules\Course;

use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Rule;

class PublicVideoCanBeViewed implements Rule
{
    private $msg;
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
        $sectionInfo = CourseChapter::where('id', $value)->where([['video_id', '>', 0]])->first();
        $this->dataOperator->setSectionInfo($sectionInfo);
        //如果课节不存在，则不允许操作
        if (empty($sectionInfo)) {
            $this->msg = __('validation.exists');
            return false;
        }
        //如果操作者没有购买课程，则不允许操作
        if (!Course::courseIsPurchasedByUser($sectionInfo['course_id'], User::getCurrentUserCache('id'))) {
            $this->msg = __('messages.didNotPurchaseCourse');
            return false;
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
