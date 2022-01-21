<?php

namespace App\Http\Requests\Recruitment\Extend;

use App\Models\Recruitment\RecruitmentResumeJobExperience;
use App\Models\User\User;

class RecruitmentResumeJobExperienceForManageResume extends \App\Http\Requests\Recruitment\RecruitmentResumeJobExperience
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            'resume_id' => [
                'exists:recruitment_resumes,id,user_id,' . User::getCurrentUserCache('id')
            ],
            'id' => [
                function ($attribute, $value, $fail) {
                    $info = RecruitmentResumeJobExperience::leftJoin(
                        'recruitment_resumes',
                        'recruitment_resume_job_experiences.resume_id',
                        'recruitment_resumes.id'
                    )->where(
                        'recruitment_resume_job_experiences.id',
                        $value
                    )->where(
                        'recruitment_resumes.user_id',
                        User::getCurrentUserCache('id')
                    )->first();
                    if (empty($info)) {
                        return $fail(__('validation.exists'));
                    }
                }
            ]
        ]);
    }
}
