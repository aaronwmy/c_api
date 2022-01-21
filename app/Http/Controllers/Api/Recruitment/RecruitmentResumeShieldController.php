<?php

namespace App\Http\Controllers\Api\Recruitment;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Recruitment\RecruitmentResumeShield;
use App\Models\Recruitment\RecruitmentResumeShieldForCompany;
use App\Models\User\User;

class RecruitmentResumeShieldController extends BaseController
{
    //增加招聘简历屏蔽公司
    public function addShield(RecruitmentResumeShield $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'shield_keyword' => [
                'required'
            ]
        ]);
        try {
            //增加招聘简历屏蔽公司
            RecruitmentResumeShieldForCompany::create([
                'user_id' => User::getCurrentUserCache('id'),
                'shield_keyword' => $input['shield_keyword']
            ]);
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //删除招聘简历屏蔽公司
    public function deleteShield(RecruitmentResumeShield $request)
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
            //删除招聘简历屏蔽公司
            RecruitmentResumeShieldForCompany::where('id', $input['id'])->delete();
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }
}
