<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\BaseController;
use App\Models\User\School;
use App\Models\User\TeacherApplySchool;
use App\Models\User\User;
use Illuminate\Http\Request;

class TeacherApplySchoolController extends BaseController
{
    //申请学校
    public function applySchool(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'school_user_id' => [
                'required',
                'exists:schools,user_id,status,' . School::APPROVED,
                'unique:teacher_apply_schools,school_user_id,0,id,user_id,' . User::getCurrentUserCache('id') . ',status,' . TeacherApplySchool::UNDER_REVIEW
            ]
        ]);
        try {
            //增加教师申请学校的数据
            TeacherApplySchool::create([
                'user_id' => User::getCurrentUserCache('id'),
                'school_user_id' => $input['school_user_id']
            ]);
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }
}
