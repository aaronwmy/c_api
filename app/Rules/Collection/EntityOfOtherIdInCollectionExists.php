<?php

namespace App\Rules\Collection;

use App\Models\Collection\Collection;
use App\Models\Course\Course;
use App\Models\Recruitment\RecruitmentPosition;
use App\Models\Recruitment\RecruitmentResume;
use Illuminate\Contracts\Validation\Rule;

class EntityOfOtherIdInCollectionExists implements Rule
{
    private $type;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($type)
    {
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
        if ($this->type == Collection::COURSE) {
            $info = Course::where('id', $value)->where('status', Course::APPROVED)->first();
        } elseif ($this->type == Collection::POSITION) {
            $info = RecruitmentPosition::where('id', $value)->first();
        } elseif ($this->type == Collection::RESUME) {
            $info = RecruitmentResume::where('id', $value)->first();
        }
        return !empty($info);
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
