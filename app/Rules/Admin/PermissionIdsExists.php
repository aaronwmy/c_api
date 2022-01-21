<?php

namespace App\Rules\Admin;

use Illuminate\Contracts\Validation\Rule;
use Spatie\Permission\Models\Permission;

class PermissionIdsExists implements Rule
{
    private $guardName;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($guardName)
    {
        $this->guardName = $guardName;
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
            if (Permission::where('id', $ids[$i])->where('guard_name', $this->guardName)->count() == 0) return false;
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
