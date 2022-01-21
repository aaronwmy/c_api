<?php

namespace App\Rules\Course;

use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\CourseOrder\CourseOrder;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Rule;

class CourseCanBePurchased implements Rule
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
        $courseInfo = Course::where('id', $value)->where('status', Course::APPROVED)->first();
        $this->dataOperator->setCourseInfo($courseInfo);
        //判断已审核的课程是否存在。如果否，则不允许操作。
        if (empty($courseInfo)) {
            $this->msg = __('validation.exists');
            return false;
        }
        //不能购买自己发布的课程
        if ($courseInfo['user_id'] == User::getCurrentUserCache('id')) {
            $this->msg = __('messages.cannotPurchaseYourOwnPublishedCourses');
            return false;
        }
        //如果用户已经购买过该课程，则不允许重复购买。
        if (CourseOrder::where(
                'course_id',
                $value
            )->where(
                'user_id',
                User::getCurrentUserCache('id')
            )->where(
                'status',
                CourseOrder::PAID
            )->count() > 0) {
            $this->msg = __('messages.repeatPurchaseOfCourses');
            return false;
        }
        //如果直播课程已开始，则不允许购买。
        if ($courseInfo['type'] == Course::LIVE) {
            if (CourseChapter::leftJoin(
                    'course_lives',
                    'course_chapters.id',
                    'course_lives.course_chapter_id'
                )->where('course_chapters.course_id', $courseInfo['id'])->where([
                    ['course_lives.begin_time', '<=', date('Y-m-d H:i:s')]
                ])->count() > 0) {
                $this->msg = __('messages.liveCourseHasStartedCannotPurchase');
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
