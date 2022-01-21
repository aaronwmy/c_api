<?php

namespace App\Http\Requests\Course\Extend;

use App\Models\User\User;
use App\Rules\Course\CourseIsNotPurchased;

class CourseForManageCourses extends \App\Http\Requests\Course\Course
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            'id' => [
                'exists:courses,id,user_id,' . User::getCurrentUserCache('id'),
                new CourseIsNotPurchased()
            ],
            'region_code' => [
                'exists:regions,region_code,level,3'
            ]
        ]);
    }
}
