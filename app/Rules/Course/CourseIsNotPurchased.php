<?php

namespace App\Rules\Course;

use App\Models\Course\Course;
use Illuminate\Contracts\Validation\Rule;

class CourseIsNotPurchased implements Rule
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
        return !Course::courseIsPurchased($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('messages.theCourseHasBeenPurchasedAndCannotBeModified');
    }
}
