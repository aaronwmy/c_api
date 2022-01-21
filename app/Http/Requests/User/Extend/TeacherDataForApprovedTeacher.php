<?php

namespace App\Http\Requests\User\Extend;

use App\Http\Requests\User\TeacherData;
use App\Rules\User\TeacherIsNotApproved;

class TeacherDataForApprovedTeacher extends TeacherData
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            'user_id' => [
                'integer',
                'min:1',
                new TeacherIsNotApproved()
            ]
        ]);
    }
}
