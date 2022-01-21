<?php

namespace App\Rules\Collection;

use App\Models\Collection\Collection;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Rule;

class CollectionIdsExists implements Rule
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
        $ids = explode(',', $value);
        for ($i = 0; $i < count($ids); $i++) {
            if (Collection::where(
                    'user_id',
                    User::getCurrentUserCache('id')
                )->where('id', $ids[$i])->count() == 0) {
                return false;
            }
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
