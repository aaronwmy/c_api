<?php

namespace App\Http\Requests\User\Extend;

use App\Http\Requests\User\SchoolData;
use App\Rules\User\SchoolIsNotApproved;

class SchoolDataForApprovedSchool extends SchoolData
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            'user_id' => [
                'integer',
                'min:1',
                new SchoolIsNotApproved()
            ]
        ]);
    }
}
