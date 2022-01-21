<?php

namespace App\Rules\User;

use App\Models\User\User;
use Illuminate\Contracts\Validation\Rule;

class WithdrawalAmountAppliedByUserIsRight implements Rule
{
    private $msg;

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
        if ($value <= 0) {
            $this->msg = __('messages.theAmountAppliedForWithdrawalMustBeGreaterThanZero');
            return false;
        }
        $userInfo = User::where('id', User::getCurrentUserCache('id'))->first();
        if ($userInfo['money'] < $value) {
            $this->msg = __('messages.withdrawalAmountCannotBeGreaterThanYourBalance');
            return false;
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
        return $this->msg;
    }
}
