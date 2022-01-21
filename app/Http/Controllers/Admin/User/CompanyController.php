<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\BaseController;
use App\Http\Requests\User\Extend\CompanyDataForApprovedCompany;
use App\Models\SiteMail\SiteMail;
use App\Models\User\Company;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends BaseController
{
    //查询公司信息
    public function getCompanyList(Request $request)
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
        if (isset($input['business_license_code'])) {
            array_push($where, ['business_license_code', 'like', '%' . $input['business_license_code'] . '%']);
        }
        if (isset($input['company_name'])) {
            array_push($where, ['company_name', 'like', '%' . $input['company_name'] . '%']);
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
            array_push($where, ['companies.status', $input['status']]);
        }
        if (isset($input['fullname'])) {
            array_push($where, ['companies.fullname', 'like', '%' . $input['fullname'] . '%']);
        }
        //查询公司的数据
        $list = Company::where($where)->join(
            'users',
            'companies.user_id',
            'users.id'
        )->select(
            'companies.*',
            'users.mobile',
            'users.portrait',
            'users.nickname',
            'users.sex',
            'users.type'
        )->paginate($pageSize);
        //返回公司的数据
        return $this->success($list);
    }

    //拒绝个人成为公司
    public function rejectCompany(CompanyDataForApprovedCompany $request)
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
            //修改公司的审核状态为已拒绝，修改公司被拒绝的原因。
            Company::where('user_id', $input['user_id'])->update([
                'status' => Company::REJECTED,
                'reasons_for_rejection' => $input['reasons_for_rejection']
            ]);
            //给公司发送系统消息
            SiteMail::sendSystemMessageToUser(
                $input['user_id'],
                __('messages.rejectCompanyMsgTitle'),
                __('messages.rejectCompanyMsgContent',
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

    //批准个人成为公司
    public function approvedCompany(CompanyDataForApprovedCompany $request)
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
            //修改公司状态为已审核
            Company::where('user_id', $input['user_id'])->update([
                'status' => Company::APPROVED
            ]);
            //修改用户类型为公司
            User::where('id', $input['user_id'])->update([
                'type' => User::COMPANY_TYPE
            ]);
            //给公司发送系统消息
            SiteMail::sendSystemMessageToUser(
                $input['user_id'],
                __('messages.approvedCompanyMsgTitle'),
                __('messages.approvedCompanyMsgContent')
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
