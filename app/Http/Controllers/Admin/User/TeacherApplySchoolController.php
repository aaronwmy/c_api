<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\BaseController;
use App\Models\User\TeacherApplySchool;
use Illuminate\Http\Request;

class TeacherApplySchoolController extends BaseController
{
    //查询教师申请学校信息
    public function getApplySchoolList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 10;
        //设置查询条件
        $where = [];
        //查询教师申请学校的数据
        $list = TeacherApplySchool::leftJoin(
            'teachers',
            'teacher_apply_schools.user_id',
            'teachers.user_id'
        )->leftJoin(
            'users',
            'teachers.user_id',
            'users.id'
        )->leftJoin(
            'schools',
            'teacher_apply_schools.school_user_id',
            'schools.user_id'
        )->where($where)->select(
            'teachers.*',
            'users.mobile',
            'users.portrait',
            'users.nickname',
            'users.sex',
            'users.type',
            'schools.school_name as apply_school_name'
        )->paginate($pageSize);
        //返回教师申请学校的数据
        return $this->success($list);
    }
}
