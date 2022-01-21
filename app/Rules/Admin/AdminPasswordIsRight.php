<?php

namespace App\Rules\Admin;

use Illuminate\Contracts\Validation\Rule;

class AdminPasswordIsRight implements Rule
{
    private $passwordInDatabase;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($passwordInDatabase)
    {
        $this->passwordInDatabase = $passwordInDatabase;
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
        return password_verify($value, $this->passwordInDatabase);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('messages.passwordError');
    }
}
