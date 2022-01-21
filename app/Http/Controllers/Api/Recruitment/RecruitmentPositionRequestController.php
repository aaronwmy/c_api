<?php

namespace App\Http\Controllers\Api\Recruitment;

use App\Http\Controllers\BaseController;
use App\Models\Recruitment\RecruitmentPositionRequest;
use App\Models\Recruitment\RecruitmentResume;
use App\Models\User\User;
use App\Rules\Recruitment\RecruitmentPositionIdsCanBeRequested;
use Illuminate\Http\Request;

class RecruitmentPositionRequestController extends BaseController
{
    //申请多个招聘职位
    public function addPositionRequests(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'recruitment_position_ids' => [
                'required',
                new RecruitmentPositionIdsCanBeRequested()
            ]
        ]);
        //查询操作者的简历
        $resumeInfo = RecruitmentResume::where('user_id', User::getCurrentUserCache('id'))->first();
        //填写简历之后，才能投递职位
        if (empty($resumeInfo)) {
            return $this->error(__('messages.fillInResumeBeforeSubmittingPosition'));
        }
        try {
            $ids = array_values(array_unique(explode(',', $input['recruitment_position_ids'])));
            for ($i = 0; $i < count($ids); $i++) {
                //增加招聘职位申请
                RecruitmentPositionRequest::create([
                    'user_id' => User::getCurrentUserCache('id'),
                    'resume_id' => $resumeInfo['id'],
                    'position_id' => $ids[$i]
                ]);
            }
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //查询申请的招聘职位
    public function getPositionRequest(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 12;
        //设置查询条件
        $where = [
            ['recruitment_position_requests.user_id', User::getCurrentUserCache('id')]
        ];
        if (isset($input['position_status'])) {
            array_push($where, ['recruitment_positions.status', $input['position_status']]);
        }
        //查询申请的招聘职位
        $list = RecruitmentPositionRequest::join(
            'recruitment_positions',
            'recruitment_position_requests.position_id',
            'recruitment_positions.id'
        )->leftJoin(
            'recruitment_experiences',
            'recruitment_positions.experience_id',
            'recruitment_experiences.id'
        )->leftJoin(
            'recruitment_education',
            'recruitment_positions.education_id',
            'recruitment_education.id'
        )->leftJoin(
            'companies',
            'recruitment_positions.user_id',
            'companies.user_id'
        )->leftJoin(
            'regions',
            'recruitment_positions.region_code',
            'regions.region_code'
        )->leftJoin(
            'users',
            'recruitment_positions.user_id',
            'users.id'
        )->where($where)->select(
            'recruitment_positions.*',
            'recruitment_experiences.experience_name',
            'recruitment_education.education_name',
            'companies.company_name',
            'companies.address as company_address',
            'regions.region_fullname',
            'users.nickname',
            'users.portrait'
        )->orderBy('recruitment_position_requests.id','desc')->paginate($pageSize);
        //返回申请的招聘职位的数据
        return $this->success($list);
    }
}
