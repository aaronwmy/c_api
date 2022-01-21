<?php

namespace App\Models\Course;

use App\Models\BaseModel;
use App\Models\CourseOrder\CourseOrder;
use App\Models\User\UserStudyCourse;
use Illuminate\Support\Facades\DB;

class Course extends BaseModel
{
    const REJECTED = -1;
    const UNDER_REVIEW = 0;
    const APPROVED = 1;
    const VIDEO = 1;
    const LIVE = 2;

    //判断课程是否已经被购买过
    protected function courseIsPurchased($course_id)
    {
        if (CourseOrder::where('course_id', $course_id)->where('status', CourseOrder::PAID)->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    //判断课程是否已经被某个用户购买过
    protected function courseIsPurchasedByUser($course_id, $user_id)
    {
        if (CourseOrder::where('user_id', $user_id)->where('course_id', $course_id)->where('status', CourseOrder::PAID)->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    //更新课程的学习人数
    protected function updateStudyPeopleNumber($course_id)
    {
        Course::where('id', $course_id)->update([
            'study_people_number' => UserStudyCourse::where('course_id', $course_id)->count(DB::raw('distinct user_id'))
        ]);
    }
}
