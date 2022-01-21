<?php

namespace App\Rules\Course;

use App\Models\Course\Course;
use Illuminate\Contracts\Validation\Rule;

class CourseIsNotApproved implements Rule
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
        $courseInfo = Course::where('id', $value)->where('status', '!=', Course::APPROVED)->first();
        $this->dataOperator->setCourseInfo($courseInfo);
        return !empty($courseInfo);
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
