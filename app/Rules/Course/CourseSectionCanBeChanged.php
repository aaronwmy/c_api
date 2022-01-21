<?php

namespace App\Rules\Course;

use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Rule;

class CourseSectionCanBeChanged implements Rule
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
        //获得课程课节的数据
        $sectionInfo = CourseChapter::where('id', $value)->where([['fid', '>', 0]])->first();
        $this->dataOperator->setSectionInfo($sectionInfo);
        //判断课程课节是否存在
        if (empty($sectionInfo)) {
            $this->msg = __('validation.exists');
            return false;
        }
        //判断课程课节是否属于操作者所有
        $courseInfo = Course::where('id', $sectionInfo['course_id'])->where('user_id', User::getCurrentUserCache('id'))->first();
        if (empty($courseInfo)) {
            $this->msg = __('validation.exists');
            return false;
        }
        $sectionInfo['course_type'] = $courseInfo['type'];
        //如果课程已经被购买，则不允许操作
        if (Course::courseIsPurchased($sectionInfo['course_id'])) {
            $this->msg = __('messages.theCourseHasBeenPurchasedAndCannotBeModified');
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
