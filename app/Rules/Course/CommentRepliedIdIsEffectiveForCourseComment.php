<?php

namespace App\Rules\Course;

use App\Models\Course\CourseComment;
use Illuminate\Contracts\Validation\Rule;

class CommentRepliedIdIsEffectiveForCourseComment implements Rule
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
        $commentInfo = CourseComment::where('id', $value)->first();
        if (empty($commentInfo)) {
            return false;
        }
        $this->dataOperator->setCommentInfo($commentInfo);
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
