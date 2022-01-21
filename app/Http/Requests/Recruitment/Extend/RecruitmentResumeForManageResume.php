<?php

namespace App\Http\Requests\Recruitment\Extend;

use App\Models\User\User;

class RecruitmentResumeForManageResume extends \App\Http\Requests\Recruitment\RecruitmentResume
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            'id' => [
                'exists:recruitment_resumes,id,user_id,' . User::getCurrentUserCache('id')
            ]
        ]);
    }
}
