<?php

namespace App\Rules\User;

use App\Models\User\UserWithdrawal;
use Illuminate\Contracts\Validation\Rule;

class UserBankCanBeDeleted implements Rule
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
        return UserWithdrawal::where('user_bank_id', $value)->where('status', UserWithdrawal::NO_WITHDRAWAL)->count() === 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('messages.withdrawalApplicationForCollectionMethodWhichCannotBeDeleted');
    }
}
