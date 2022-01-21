<?php

namespace App\Rules\Course;

use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use Illuminate\Contracts\Validation\Rule;

class SectionIdIsEffectiveForCourseComment implements Rule
{
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
        $sectionInfo = CourseChapter::where('id', $value)->where('fid', '>', 0)->first();
        if (empty($sectionInfo)) {
            return false;
        }
        $courseInfo = Course::where('id', $sectionInfo['course_id'])->where('status', Course::APPROVED)->first();
        if (empty($courseInfo)) {
            return false;
        }
        $this->dataOperator->setCourseInfo($courseInfo);
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.exists');
    }
}
