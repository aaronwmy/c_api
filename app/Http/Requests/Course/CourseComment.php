<?php

namespace App\Http\Requests\Course;

use App\Http\Requests\BaseFormRequest;
use App\Rules\Course\CommentRepliedIdIsEffectiveForCourseComment;
use App\Rules\Course\SectionIdIsEffectiveForCourseComment;

class CourseComment extends BaseFormRequest
{
    protected $attributeNamesOfDataOperator = ['courseInfo', 'commentInfo'];

    public function rules()
    {
        return [
            'section_id' => [
                new SectionIdIsEffectiveForCourseComment($this->getDataOperator())
            ],
            'comment_replied_id' => [
                new CommentRepliedIdIsEffectiveForCourseComment($this->getDataOperator())
            ]
        ];
    }
}
