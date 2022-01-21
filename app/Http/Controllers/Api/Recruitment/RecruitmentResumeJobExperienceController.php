<?php

namespace App\Http\Controllers\Api\Recruitment;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Recruitment\Extend\RecruitmentResumeJobExperienceForManageResume;
use App\Models\Recruitment\RecruitmentResumeJobExperience;
use Illuminate\Http\Request;

class RecruitmentResumeJobExperienceController extends BaseController
{
    //增加招聘简历的工作经历
    public function addResumeJobExperience(RecruitmentResumeJobExperienceForManageResume $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'resume_id' => [
                'required'
            ],
            'company_name' => [
                'required'
            ],
            'position_type_id' => [
                'required'
            ],
            'entry_year_month' => [
                'required'
            ],
            'departure_year_month' => [
                'required'
            ],
            'job_content' => [
                'required'
            ],
            'is_shield_resume' => [
                'required'
            ]
        ]);
        try {
            //增加招聘简历的工作经历
            RecruitmentResumeJobExperience::create([
                'company_name' => $input['company_name'],
                'resume_id' => $input['resume_id'],
                'position_type_id' => $input['position_type_id'],
                'entry_year_month' => $input['entry_year_month'],
                'departure_year_month' => $input['departure_year_month'],
                'job_content' => $input['job_content'],
                'is_shield_resume' => $input['is_shield_resume']
            ]);
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //修改招聘简历的工作经历
    public function editResumeJobExperience(RecruitmentResumeJobExperienceForManageResume $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'id' => [
                'required'
            ],
            'company_name' => [
                'required'
            ],
            'position_type_id' => [
                'required'
            ],
            'entry_year_month' => [
                'required'
            ],
            'departure_year_month' => [
                'required'
            ],
            'job_content' => [
                'required'
            ],
            'is_shield_resume' => [
                'required'
            ]
        ]);
        try {
            //修改招聘简历的工作经历
            RecruitmentResumeJobExperience::where('id', $input['id'])->update([
                'company_name' => $input['company_name'],
                'position_type_id' => $input['position_type_id'],
                'entry_year_month' => $input['entry_year_month'],
                'departure_year_month' => $input['departure_year_month'],
                'job_content' => $input['job_content'],
                'is_shield_resume' => $input['is_shield_resume']
            ]);
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //删除招聘简历的工作经历
    public function deleteResumeJobExperience(RecruitmentResumeJobExperienceForManageResume $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'id' => [
                'required'
            ]
        ]);
        try {
            //删除招聘简历的工作经历
            RecruitmentResumeJobExperience::where('id', $input['id'])->delete();
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //查询招聘简历的工作经历
    public function getResumeJobExperience(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'id' => [
                'required_without:resume_id'
            ],
            'resume_id' => [
                'required_without:id'
            ]
        ]);
        //设置查询条件
        $where = [];
        if (isset($input['resume_id'])) {
            array_push($where, ['recruitment_resume_job_experiences.resume_id', $input['resume_id']]);
        }
        if (isset($input['id'])) {
            array_push($where, ['recruitment_resume_job_experiences.id', $input['id']]);
        }
        //查询招聘简历的工作经历
        $list = RecruitmentResumeJobExperience::leftJoin(
            'recruitment_position_types',
            'recruitment_resume_job_experiences.position_type_id',
            'recruitment_position_types.id'
        )->where($where)->select('recruitment_resume_job_experiences.*', 'recruitment_position_types.type_name')->get();
        //返回招聘简历的工作经历
        return $this->success($list);
    }
}
