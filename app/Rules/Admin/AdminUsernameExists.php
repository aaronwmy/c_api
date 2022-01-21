<?php

namespace App\Rules\Admin;

use App\Models\AdminUser\AdminUser;
use Illuminate\Contracts\Validation\Rule;

class AdminUsernameExists implements Rule
{
    private $userInfo;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($userInfo = null)
    {
        $this->userInfo = $userInfo;
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
        if (empty($this->userInfo)) {
            $this->userInfo = AdminUser::where('username', $value)->first();
        }
        return !empty($this->userInfo);
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
