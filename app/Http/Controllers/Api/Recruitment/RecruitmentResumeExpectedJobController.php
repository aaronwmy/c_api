<?php

namespace App\Http\Controllers\Api\Recruitment;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Recruitment\Extend\RecruitmentResumeExpectedJobForManageResume;
use App\Models\Recruitment\RecruitmentResume;
use App\Models\Recruitment\RecruitmentResumeExpectedJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecruitmentResumeExpectedJobController extends BaseController
{
    //增加招聘简历的期望工作
    public function addResumeExpectedJob(RecruitmentResumeExpectedJobForManageResume $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'resume_id' => [
                'required'
            ],
            'position_type_id' => [
                'required'
            ],
            'min_salary' => [
                'required'
            ],
            'max_salary' => [
                'required'
            ],
            'job_type' => [
                'required'
            ],
            'region_code' => [
                'required'
            ]
        ]);
        //开启数据库事务
        DB::beginTransaction();
        try {
            //增加招聘简历的期望工作
            RecruitmentResumeExpectedJob::create([
                'resume_id' => $input['resume_id'],
                'position_type_id' => $input['position_type_id'],
                'min_salary' => $input['min_salary'],
                'max_salary' => $input['max_salary'],
                'job_type' => $input['job_type'],
                'region_code' => $input['region_code']
            ]);
            //更新简历的关键的期望工作
            RecruitmentResume::updateImportantExpectedJob($input['resume_id']);
            //提交数据库事务
            DB::commit();
        } catch (\Exception $e) {
            //数据库事务回滚
            DB::rollback();
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //修改招聘简历的期望工作
    public function editResumeExpectedJob(RecruitmentResumeExpectedJobForManageResume $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'id' => [
                'required'
            ],
            'position_type_id' => [
                'required'
            ],
            'min_salary' => [
                'required'
            ],
            'max_salary' => [
                'required'
            ],
            'job_type' => [
                'required'
            ],
            'region_code' => [
                'required'
            ]
        ]);
        //开启数据库事务
        DB::beginTransaction();
        try {
            //修改招聘简历的期望工作
            $info = RecruitmentResumeExpectedJob::where('id', $input['id'])->first();
            $info['position_type_id'] = $input['position_type_id'];
            $info['min_salary'] = $input['min_salary'];
            $info['max_salary'] = $input['max_salary'];
            $info['job_type'] = $input['job_type'];
            $info['region_code'] = $input['region_code'];
            $info->save();
            //更新简历的关键的期望工作
            RecruitmentResume::updateImportantExpectedJob($info['resume_id']);
            //提交数据库事务
            DB::commit();
        } catch (\Exception $e) {
            //数据库事务回滚
            DB::rollback();
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //删除招聘简历的期望工作
    public function deleteResumeExpectedJob(RecruitmentResumeExpectedJobForManageResume $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'id' => [
                'required'
            ]
        ]);
        //开启数据库事务
        DB::beginTransaction();
        try {
            $info = RecruitmentResumeExpectedJob::where('id', $input['id'])->first();
            //修改招聘简历的期望工作
            RecruitmentResumeExpectedJob::where('id', $input['id'])->delete();
            //更新简历的关键的期望工作
            RecruitmentResume::updateImportantExpectedJob($info['resume_id']);
            //提交数据库事务
            DB::commit();
        } catch (\Exception $e) {
            //数据库事务回滚
            DB::rollback();
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //查询招聘简历的期望工作
    public function getResumeExpectedJob(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置查询条件
        $where = [];
        if (isset($input['resume_id'])) {
            array_push($where, ['recruitment_resume_expected_jobs.resume_id', $input['resume_id']]);
        }
        if (isset($input['id'])) {
            array_push($where, ['recruitment_resume_expected_jobs.id', $input['id']]);
        }
        //查询招聘简历的期望工作
        $list = RecruitmentResumeExpectedJob::leftJoin(
            'recruitment_position_types',
            'recruitment_resume_expected_jobs.position_type_id',
            'recruitment_position_types.id'
        )->leftJoin(
            'regions',
            'recruitment_resume_expected_jobs.region_code',
            'regions.region_code'
        )->where($where)->select(
            'recruitment_resume_expected_jobs.*',
            'recruitment_position_types.type_name',
            'regions.region_fullname'
        )->orderBy('recruitment_resume_expected_jobs.id', 'asc')->get();
        //返回招聘简历的期望工作
        return $this->success($list);
    }
}
