<?php

namespace App\Http\Requests\Course\Extend;

use App\Rules\Course\CourseChapterCanBeChanged;
use App\Rules\Course\CourseSectionCanBeChanged;

class CourseChapterForManageSections extends CourseChapterForManageChaptersAndSections
{
    protected $attributeNamesOfDataOperator = ['chapterInfo', 'sectionInfo'];

    public function rules()
    {
        return array_merge(parent::rules(), [
            'fid' => [
                new CourseChapterCanBeChanged($this->getDataOperator())
            ],
            'id' => [
                new CourseSectionCanBeChanged($this->getDataOperator())
            ]
        ]);
    }
}
