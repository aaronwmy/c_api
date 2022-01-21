<?php

namespace App\Rules\Course;

use App\Models\Course\Course;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Rule;

class CourseExists implements Rule
{
    const PUBLIC = 1;
    const PRIVATE = 2;

    private $dataOperator;
    private $type;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($dataOperator, $type)
    {
        $this->dataOperator = $dataOperator;
        $this->type = $type;
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
        $where = [
            ['id', $value]
        ];
        if ($this->type == self::PUBLIC) array_push($where, ['status', Course::APPROVED]);
        elseif ($this->type == self::PRIVATE) array_push($where, ['user_id', User::getCurrentUserCache('id')]);
        $courseInfo = Course::where($where)->first();
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
