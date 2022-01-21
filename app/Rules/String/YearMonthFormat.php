<?php

namespace App\Rules\String;

use Illuminate\Contracts\Validation\Rule;

class YearMonthFormat implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($canBeZero = false)
    {
        $this->canBeZero = $canBeZero;
    }

    private $canBeZero;

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $pattern = "/^(19|20)\d{2}(0[1-9]|1[0-2])$/";
        if ($this->canBeZero) {
            $pattern = "/^((19|20)\d{2}(0[1-9]|1[0-2]))|0$/";
        }
        return preg_match($pattern, $value) ? true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.regex');
    }
}
