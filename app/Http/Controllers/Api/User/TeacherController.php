<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\BaseController;
use App\Http\Requests\User\TeacherData;
use App\Models\User\Company;
use App\Models\User\School;
use App\Models\User\Teacher;
use App\Models\User\TeacherApplySchool;
use App\Models\User\User;
use App\Services\DatabaseResponse\DatabaseResponseService;
use Illuminate\Http\Request;

class TeacherController extends BaseController
{
    //获得教师信息
    public function me()
    {
        $info = Teacher::leftJoin(
            'schools',
            'teachers.school_user_id',
            'schools.user_id'
        )->leftJoin('users', 'teachers.user_id', 'users.id')->where(
            'teachers.user_id',
            User::getCurrentUserCache('id')
        )->select('teachers.*', 'schools.school_name', 'users.nickname', 'users.portrait')->first();
        if (!empty($info)) {
            $applyInfo = TeacherApplySchool::where(
                'user_id',
                User::getCurrentUserCache('id')
            )->orderBy('created_at', 'desc')->first();
            $info['apply_status'] = TeacherApplySchool::APPROVED;
            $info['apply_reasons_for_rejection'] = '';
            if (!empty($applyInfo)) {
                $info['apply_status'] = $applyInfo['status'];
                $info['apply_reasons_for_rejection'] = $applyInfo['reasons_for_rejection'];
            }
        }
        return $this->success($info);
    }

    //增加或修改教师信息
    public function addOrEditTeacher(TeacherData $request)
    {
        //获得参数
        $input = $request->all();
        $request->validate([
            'id_card_number' => [
                'required'
            ],
            'id_card_front_pic' => [
                'required'
            ],
            'id_card_back_pic' => [
                'required'
            ],
            'introduction' => [
                'required'
            ],
            'fullname' => [
                'required',
                'max:10'
            ]
        ]);
        if (School::where('user_id', User::getCurrentUserCache('id'))->count() > 0
            || Company::where('user_id', User::getCurrentUserCache('id'))->count() > 0
        ) {
            return $this->error(__('messages.YouHaveSubmittedSchoolOrCompanyInformationAndCannotSubmitTeacherInformation'));
        }
        try {
            Teacher::updateOrCreate(['user_id' => User::getCurrentUserCache('id')], [
                'id_card_number' => $input['id_card_number'],
                'id_card_front_pic' => $input['id_card_front_pic'],
                'id_card_back_pic' => $input['id_card_back_pic'],
                'introduction' => $input['introduction'],
                'fullname' => $input['fullname'],
                'status' => Teacher::UNDER_REVIEW,
                'school_user_id' => isset($input['school_user_id']) ? $input['school_user_id'] : 0
            ]);
        } catch (\Exception $e) {
            return $this->error(__('messages.DatabaseError'));
        }
        return $this->success();
    }

    //查询公共的教师
    public function getPublicList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 10;
        //设置查询条件
        $where = [
            ['teachers.status', Teacher::APPROVED]
        ];
        if (isset($input['fullname'])) {
            array_push($where, ['teachers.fullname', 'like', '%' . $input['fullname'] . '%']);
        }
        if (isset($input['user_id'])) {
            array_push($where, ['teachers.user_id', $input['user_id']]);
        }
        if (isset($input['school_user_id'])) {
            array_push($where, ['teachers.school_user_id', $input['school_user_id']]);
        }
        //设置排序条件
        $orderArr = ['teachers.created_at' => 'desc'];
        //查询公司数据
        $list = Teacher::leftJoin(
            'users',
            'teachers.user_id',
            'users.id'
        )->where($where)->ordersBy($orderArr)->select(
            'teachers.*',
            'users.portrait',
            'users.nickname',
            'users.approved_course_count',
            'users.follow_user_count'
        )->paginate($pageSize);
        //检查是否关注过该教师
        if (isset($input['check_if_followed']) && $input['check_if_followed'] == 1) {
            DatabaseResponseService::preSetIsFollowed('user_id')->exec($list);
        }
        return $this->success($list);
    }
}
