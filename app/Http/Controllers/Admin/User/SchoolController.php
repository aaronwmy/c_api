<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\BaseController;
use App\Http\Requests\User\Extend\SchoolDataForApprovedSchool;
use App\Models\SiteMail\SiteMail;
use App\Models\User\School;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SchoolController extends BaseController
{
    //查询学校信息
    public function getSchoolList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 10;
        //设置查询条件
        $where = [];
        if (isset($input['enterprise_legal_person_certificate_code'])) {
            array_push($where, ['enterprise_legal_person_certificate_code', 'like', '%' . $input['enterprise_legal_person_certificate_code'] . '%']);
        }
        if (isset($input['school_name'])) {
            array_push($where, ['school_name', 'like', '%' . $input['school_name'] . '%']);
        }
        if (isset($input['address'])) {
            array_push($where, ['address', 'like', '%' . $input['address'] . '%']);
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
            array_push($where, ['schools.status', $input['status']]);
        }
        if (isset($input['fullname'])) {
            array_push($where, ['schools.fullname', 'like', '%' . $input['fullname'] . '%']);
        }
        //查询学校的数据
        $list = School::where($where)->join(
            'users',
            'schools.user_id',
            'users.id'
        )->select(
            'schools.*',
            'users.mobile',
            'users.portrait',
            'users.nickname',
            'users.sex',
            'users.type'
        )->paginate($pageSize);
        //返回学校的数据
        return $this->success($list);
    }

    //拒绝个人成为学校
    public function rejectSchool(SchoolDataForApprovedSchool $request)
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
            //修改学校的审核状态为已拒绝，修改学校被拒绝的原因。
            School::where('user_id', $input['user_id'])->update([
                'status' => School::REJECTED,
                'reasons_for_rejection' => $input['reasons_for_rejection']
            ]);
            //给学校发送系统消息
            SiteMail::sendSystemMessageToUser(
                $input['user_id'],
                __('messages.rejectSchoolMsgTitle'),
                __('messages.rejectSchoolMsgContent',
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

    //批准个人成为学校
    public function approvedSchool(SchoolDataForApprovedSchool $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'user_id' => [
                'required'
            ]
        ]);
        //开启数据库事务
        DB::beginTransaction();
        try {
            //修改学校状态为已审核
            School::where('user_id', $input['user_id'])->update([
                'status' => School::APPROVED
            ]);
            //修改用户类型为学校
            User::where('id', $input['user_id'])->update([
                'type' => User::SCHOOL_TYPE
            ]);
            //给学校发送系统消息
            SiteMail::sendSystemMessageToUser(
                $input['user_id'],
                __('messages.approvedSchoolMsgTitle'),
                __('messages.approvedSchoolMsgContent')
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
