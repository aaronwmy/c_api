<?php

namespace App\Rules\Course;

use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Rule;

class CourseChapterCanBeChanged implements Rule
{
    private $msg;
    private $dataOperator;
    private $isDelete;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($dataOperator, $isDelete = false)
    {
        $this->dataOperator = $dataOperator;
        $this->isDelete = $isDelete;
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
        //获得课程章的数据
        $chapterInfo = \App\Models\Course\CourseChapter::where('id', $value)->where('fid', 0)->first();
        $this->dataOperator->setChapterInfo($chapterInfo);
        //判断课程章是否存在
        if (empty($chapterInfo)) {
            $this->msg = __('validation.exists');
            return false;
        }
        //判断课程章是否属于操作者所有
        $courseInfo = Course::where('id', $chapterInfo['course_id'])->where('user_id', User::getCurrentUserCache('id'))->first();
        if (empty($courseInfo)) {
            $this->msg = __('validation.exists');
            return false;
        }
        $chapterInfo['course_type'] = $courseInfo['type'];
        //如果课程已经被购买，则不允许操作
        if (Course::courseIsPurchased($chapterInfo['course_id'])) {
            $this->msg = __('messages.theCourseHasBeenPurchasedAndCannotBeModified');
            return false;
        }
        //课程章下面如果存在课节，则不能删除课程章
        if ($this->isDelete) {
            if (CourseChapter::where('fid', $value)->count() > 0) {
                $this->msg = __('messages.canDeleteChapterOnlyAfterDeletingTheSectionsUnderTheChapter');
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
