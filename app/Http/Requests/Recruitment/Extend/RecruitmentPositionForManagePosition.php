<?php

namespace App\Http\Requests\Recruitment\Extend;

use App\Models\User\User;

class RecruitmentPositionForManagePosition extends \App\Http\Requests\Recruitment\RecruitmentPosition
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            'id' => [
                'exists:recruitment_positions,id,user_id,' . User::getCurrentUserCache('id')
            ]
        ]);
    }
}
