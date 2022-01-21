<?php

namespace App\Rules\Collection;

use App\Models\Collection\Collection;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Rule;

class OtherIdInCollectionExists implements Rule
{
    private $type;
    private $dataOperator;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($type, $dataOperator)
    {
        $this->type = $type;
        $this->dataOperator = $dataOperator;
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
        $collectionInfo = Collection::where(
            'user_id',
            User::getCurrentUserCache('id')
        )->where('type', $this->type)->where('other_id', $value)->first();
        $this->dataOperator->setCollectionInfo($collectionInfo);
        return !empty($collectionInfo);
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
