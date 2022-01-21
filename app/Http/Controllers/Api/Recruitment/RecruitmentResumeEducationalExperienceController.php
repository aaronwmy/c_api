<?php

namespace App\Http\Controllers\Api\Recruitment;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Recruitment\Extend\RecruitmentResumeEducationalExperienceForManageResume;
use App\Models\Recruitment\RecruitmentResumeEducationalExperience;
use Illuminate\Http\Request;

class RecruitmentResumeEducationalExperienceController extends BaseController
{
    //增加招聘简历的教育经历
    public function addResumeEducationalExperience(RecruitmentResumeEducationalExperienceForManageResume $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'resume_id' => [
                'required'
            ],
            'school_name' => [
                'required'
            ],
            'education_id' => [
                'required'
            ],
            'admission_year_month' => [
                'required'
            ],
            'graduation_year_month' => [
                'required'
            ]
        ]);
        try {
            //添加招聘简历的教育经历
            RecruitmentResumeEducationalExperience::create([
                'resume_id' => $input['resume_id'],
                'school_name' => $input['school_name'],
                'education_id' => $input['education_id'],
                'major' => isset($input['major']) ? $input['major'] : '',
                'admission_year_month' => $input['admission_year_month'],
                'graduation_year_month' => $input['graduation_year_month']
            ]);
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //修改招聘简历的教育经历
    public function editResumeEducationalExperience(RecruitmentResumeEducationalExperienceForManageResume $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'id' => [
                'required'
            ],
            'school_name' => [
                'required'
            ],
            'education_id' => [
                'required'
            ],
            'admission_year_month' => [
                'required'
            ],
            'graduation_year_month' => [
                'required'
            ]
        ]);
        try {
            //修改招聘简历的教育经历
            RecruitmentResumeEducationalExperience::where('id', $input['id'])->update([
                'school_name' => $input['school_name'],
                'education_id' => $input['education_id'],
                'major' => isset($input['major']) ? $input['major'] : '',
                'admission_year_month' => $input['admission_year_month'],
                'graduation_year_month' => $input['graduation_year_month']
            ]);
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //删除招聘简历的教育经历
    public function deleteResumeEducationalExperience(RecruitmentResumeEducationalExperienceForManageResume $request)
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
            //删除招聘简历的教育经历
            RecruitmentResumeEducationalExperience::where('id', $input['id'])->delete();
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //查询招聘简历的教育经历
    public function getResumeEducationalExperience(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置查询条件
        $where = [];
        if (isset($input['resume_id'])) {
            array_push($where, ['recruitment_resume_educational_experiences.resume_id', $input['resume_id']]);
        }
        if (isset($input['id'])) {
            array_push($where, ['recruitment_resume_educational_experiences.id', $input['id']]);
        }
        //查询招聘简历的教育经历
        $list = RecruitmentResumeEducationalExperience::leftJoin(
            'recruitment_education',
            'recruitment_resume_educational_experiences.education_id',
            'recruitment_education.id'
        )->where($where)->select('recruitment_resume_educational_experiences.*','recruitment_education.education_name')->get();
        //返回招聘简历的教育经历
        return $this->success($list);
    }
}
