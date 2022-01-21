<?php

namespace App\Http\Requests\Course\Extend;

use App\Http\Requests\Course\CourseChapter;
use App\Models\User\User;
use App\Rules\Course\CourseIsNotPurchased;

class CourseChapterForManageChaptersAndSections extends CourseChapter
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            'course_id' => [
                'exists:courses,id,user_id,' . User::getCurrentUserCache('id'),
                new CourseIsNotPurchased()
            ]
        ]);
    }
}
