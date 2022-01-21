<?php

namespace App\Http\Requests\Recruitment;

use App\Http\Requests\BaseFormRequest;
use App\Models\User\User;

class RecruitmentResumeShield extends BaseFormRequest
{
    public function rules()
    {
        return [
            'shield_keyword' => [
                'unique:recruitment_resume_shield_for_companies,shield_keyword,0,id,user_id,' . User::getCurrentUserCache('id')
            ],
            'id' => [
                'exists:recruitment_resume_shield_for_companies,id,user_id,' . User::getCurrentUserCache('id')
            ]
        ];
    }
}
