<?php

namespace App\Rules\Course;

use App\Models\Course\CourseChapter;
use Illuminate\Contracts\Validation\Rule;

class CourseCanBeDeleted implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        return CourseChapter::where('course_id', $value)->count() == 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('messages.canDeleteCourseOnlyAfterDeletingTheChaptersUnderTheCourse');
    }
}
