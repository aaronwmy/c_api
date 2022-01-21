<?php

namespace App\Http\Requests\Recruitment\Extend;

use App\Models\Recruitment\RecruitmentResumeExpectedJob;
use App\Models\User\User;

class RecruitmentResumeExpectedJobForManageResume extends \App\Http\Requests\Recruitment\RecruitmentResumeExpectedJob
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            'resume_id' => [
                'exists:recruitment_resumes,id,user_id,' . User::getCurrentUserCache('id')
            ],
            'id' => [
                function ($attribute, $value, $fail) {
                    $info = RecruitmentResumeExpectedJob::leftJoin(
                        'recruitment_resumes',
                        'recruitment_resume_expected_jobs.resume_id',
                        'recruitment_resumes.id'
                    )->where(
                        'recruitment_resume_expected_jobs.id',
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
