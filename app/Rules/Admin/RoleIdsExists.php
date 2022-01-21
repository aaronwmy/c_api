<?php

namespace App\Rules\Admin;

use Illuminate\Contracts\Validation\Rule;
use Spatie\Permission\Models\Role;

class RoleIdsExists implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {

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
        $ids = explode(',', $value);
        for ($i = 0; $i < count($ids); $i++) {
            if (Role::where('id', $ids[$i])->where('guard_name', 'admin')->count() == 0) return false;
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
