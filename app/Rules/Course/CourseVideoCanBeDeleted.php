<?php

namespace App\Rules\Course;

use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\Course\CourseVideo;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Rule;

class CourseVideoCanBeDeleted implements Rule
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
        $videoInfo = CourseVideo::where('id', $value)->where('user_id', User::getCurrentUserCache('id'))->first();
        $this->dataOperator->setVideoInfo($videoInfo);
        //只能删除操作者自己的课程视频
        if (empty($videoInfo)) {
            $this->msg = __('validation.exists');
            return false;
        }
        //如果视频正在被使用，则不能删除
        if (CourseChapter::join('courses', 'course_chapters.course_id', 'courses.id')->where('video_id', $value)->where('courses.type', Course::VIDEO)->count() > 0) {
            $this->msg = __('messages.theCourseVideoIsInUseAndCanNotBeDeleted');
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
