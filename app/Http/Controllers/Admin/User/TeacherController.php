<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\BaseController;
use App\Http\Requests\User\Extend\TeacherDataForApprovedTeacher;
use App\Models\SiteMail\SiteMail;
use App\Models\User\Teacher;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherController extends BaseController
{
    //查询教师信息
    public function getTeacherList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 10;
        //设置查询条件
        $where = [];
        if (isset($input['id_card_number'])) {
            array_push($where, ['id_card_number', 'like', '%' . $input['id_card_number'] . '%']);
        }
        if (isset($input['mobile'])) {
            array_push($where, ['users.mobile', 'like', '%' . $input['mobile'] . '%']);
        }
        if (isset($input['nickname'])) {
            array_push($where, ['users.nickname', 'like', '%' . $input['nickname'] . '%']);
        }
        if (isset($input['sex'])) {
            array_push($where, ['users.sex', $input['sex']]);
        }
        if (isset($input['status'])) {
            array_push($where, ['teachers.status', $input['status']]);
        }
        if (isset($input['fullname'])) {
            array_push($where, ['teachers.fullname', 'like', '%' . $input['fullname'] . '%']);
        }
        //查询教师的数据
        $list = Teacher::where($where)->join(
            'users',
            'teachers.user_id',
            'users.id'
        )->select(
            'teachers.*',
            'users.mobile',
            'users.portrait',
            'users.nickname',
            'users.sex',
            'users.type'
        )->paginate($pageSize);
        //返回教师的数据
        return $this->success($list);
    }

    //拒绝个人成为教师
    public function rejectTeacher(TeacherDataForApprovedTeacher $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'user_id' => [
                'required'
            ],
            'reasons_for_rejection' => [
                'required'
            ]
        ]);
        //开启数据库事务
        DB::beginTransaction();
        try {
            //修改教师的审核状态为已拒绝，修改教师被拒绝的原因。
            Teacher::where('user_id', $input['user_id'])->update([
                'status' => Teacher::REJECTED,
                'reasons_for_rejection' => $input['reasons_for_rejection']
            ]);
            //给教师发送系统消息
            SiteMail::sendSystemMessageToUser(
                $input['user_id'],
                __('messages.rejectTeacherMsgTitle'),
                __('messages.rejectTeacherMsgContent',
                    [
                        'value1' => $input['reasons_for_rejection']
                    ]
                )
            );
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

    //批准个人成为教师
    public function approvedTeacher(TeacherDataForApprovedTeacher $request)
    {
        //获得参数
        $input = $request->all();
        $request->validate([
            'user_id' => [
                'required'
            ]
        ]);
        //开启数据库事务
        DB::beginTransaction();
        try {
            //修改教师状态为已审核
            Teacher::where('user_id', $input['user_id'])->update([
                'status' => Teacher::APPROVED
            ]);
            //修改用户类型为教师
            User::where('id', $input['user_id'])->update([
                'type' => User::TEACHER_TYPE
            ]);
            //给教师发送系统消息
            SiteMail::sendSystemMessageToUser(
                $input['user_id'],
                __('messages.approvedTeacherMsgTitle'),
                __('messages.approvedTeacherMsgContent')
            );
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
}
