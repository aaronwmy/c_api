<?php

namespace App\Models\Recruitment;

use App\Models\BaseModel;
use App\Models\User\Company;
use App\Models\User\User;
use App\Services\String\StringService;
use Illuminate\Support\Facades\DB;

class RecruitmentResume extends BaseModel
{
    //给单个简历信息增加返回值
    protected function infoModifyReturnValue($info, $hideContact = true)
    {
        if (isset($info['birth_year_month'])) {
            $birthYear = (int)substr($info['birth_year_month'], 0, 4);
            $birthMonth = (int)substr($info['birth_year_month'], 4, 2);
            $info['age'] = (int)date('Y') - $birthYear;
            if ($birthMonth > (int)date('m')) {
                $info['age'] = $info['age'] - 1;
            }
        }
        if (isset($info['first_job_year_month'])) {
            $firstJobYear = (int)substr($info['first_job_year_month'], 0, 4);
            $firstJobMonth = (int)substr($info['first_job_year_month'], 4, 2);
            $info['working_years'] = (int)date('Y') - $firstJobYear;
            if ($firstJobMonth > (int)date('m')) {
                $info['working_years'] = $info['working_years'] - 1;
            }
        }
        if ($hideContact) {
            if (isset($info['mobile'])) {
                $info['mobile'] = StringService::hideStarMobile($info['mobile']);
            }
            if (isset($info['email'])) {
                $info['email'] = StringService::hideStarEmail($info['email']);
            }
        }
        return $info;
    }

    //给简历列表信息增加返回值
    protected function listModifyReturnValue($list, $hideContact = true)
    {
        for ($i = 0; $i < count($list); $i++) {
            $list[$i] = $this->infoModifyReturnValue($list[$i], $hideContact);
        }
        return $list;
    }

    //更新简历中的关键的期望工作
    protected function updateImportantExpectedJob($id)
    {
        $jobList = RecruitmentResumeExpectedJob::where('resume_id', $id)->orderBy('id', 'asc')->get();
        $importantExpectedJobId = 0;
        for ($i = 0; $i < count($jobList); $i++) {
            if ($jobList[$i]['job_type'] == RecruitmentResumeExpectedJob::FULL_TIME_JOB) {
                $importantExpectedJobId = $jobList[$i]['id'];
                break;
            }
        }
        if ($importantExpectedJobId == 0 && count($jobList) > 0) {
            $importantExpectedJobId = $jobList[0]['id'];
        }
        RecruitmentResume::where('id', $id)->update(['important_expected_job_id' => $importantExpectedJobId]);
    }

    //获得应该对操作者屏蔽的简历的所有者的用户id数组
    protected function getShieldResumeUserIds()
    {
        //查询操作者的公司信息
        $companyInfo = Company::where('user_id', User::getCurrentUserCache('id'))->first();
        $userIds = RecruitmentResumeJobExperience::leftJoin(
            'recruitment_resumes',
            'recruitment_resume_job_experiences.resume_id',
            'recruitment_resumes.id'
        )->where('recruitment_resume_job_experiences.is_shield_resume', 1)->whereRaw(
            "? LIKE CONCAT('%',company_name,'%')",
            $companyInfo['company_name']
        )->first([DB::raw('group_concat(distinct recruitment_resumes.user_id) as user_ids')])['user_ids'];
        $userIds = empty($userIds) ? [] : explode(',', $userIds);
        return $userIds;
    }
}
