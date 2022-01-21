<?php

namespace App\Http\Requests\Course\Extend;

use App\Rules\Course\CourseIsNotApproved;

class CourseForApprovedCourses extends \App\Http\Requests\Course\Course
{
    protected $attributeNamesOfDataOperator = ['courseInfo'];

    public function rules()
    {
        return array_merge(parent::rules(), [
            'course_id' => [
                new CourseIsNotApproved($this->getDataOperator())
            ]
        ]);
    }
}
