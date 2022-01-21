<?php

namespace App\Rules\Course;

use App\Models\Course\CourseChapter;
use Illuminate\Contracts\Validation\Rule;

class SortsParamVerificationOfCourseChapter implements Rule
{
    private $courseId;
    private $dataOperator;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($courseId, $dataOperator)
    {
        $this->courseId = $courseId;
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
        $sorts = json_decode(htmlspecialchars_decode($value), true);
        $this->dataOperator->setSorts($sorts);
        if (empty($sorts)) {
            return false;
        }
        foreach ($sorts as $key => $value) {
            if (CourseChapter::where('id', $key)->where('course_id', $this->courseId)->where('fid', 0)->count() == 0) {
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
        return __('validation.exists');
    }
}
