<?php

namespace App\Http\Requests\Course\Extend;

use App\Rules\Course\CourseChapterCanBeChanged;

class CourseChapterForDeleteChapters extends CourseChapterForManageChaptersAndSections
{
    protected $attributeNamesOfDataOperator = ['chapterInfo'];

    public function rules()
    {
        return array_merge(parent::rules(), [
            'id' => [
                new CourseChapterCanBeChanged($this->getDataOperator(), true)
            ]
        ]);
    }
}
