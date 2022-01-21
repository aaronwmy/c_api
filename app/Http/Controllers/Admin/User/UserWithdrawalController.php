<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\BaseController;
use App\Models\User\UserWithdrawal;
use Illuminate\Http\Request;

class UserWithdrawalController extends BaseController
{
    //查询用户提现数据
    public function getList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 10;
        //设置查询条件
        $where = [];
        if (isset($input['status'])) {
            array_push($where, ['user_withdrawals.status', '=', $input['status']]);
        }
        if (isset($input['fullname'])) {
            array_push($where, ['user_banks.fullname', 'like', '%' . $input['fullname'] . '%']);
        }
        if (isset($input['mobile'])) {
            array_push($where, ['users.mobile', 'like', '%' . $input['mobile'] . '%']);
        }
        if (isset($input['nickname'])) {
            array_push($where, ['users.nickname', 'like', '%' . $input['nickname'] . '%']);
        }
        if (isset($input['begin_time'])) {
            array_push($where, ['user_withdrawals.created_at', '>=', $input['begin_time']]);
        }
        if (isset($input['end_time'])) {
            array_push($where, ['user_withdrawals.created_at', '<=', $input['end_time']]);
        }
        if (isset($input['user_id'])) {
            array_push($where, ['user_withdrawals.user_id', $input['user_id']]);
        }
        $preList = UserWithdrawal::leftJoin(
            'users',
            'user_withdrawals.user_id',
            'users.id'
        )->leftJoin(
            'user_banks',
            'user_withdrawals.user_bank_id',
            'user_banks.id'
        )->leftJoin(
            'banks',
            'user_banks.bank_id',
            'banks.id'
        )->leftJoin(
            'regions',
            'user_banks.region_code',
            'regions.region_code'
        )->where($where);
        //查询用户提现数据
        $list = (clone $preList)->select(
            'user_withdrawals.*',
            'users.mobile',
            'users.nickname',
            'user_banks.fullname',
            'user_banks.branch_address',
            'user_banks.bank_card_number',
            'banks.bank_name',
            'regions.region_name'
        )->paginate($pageSize)->toArray();
        //查询用户已提现的总金额
        $list['sum_withdrawn_amount'] = (clone $preList)->where('status', UserWithdrawal::WITHDRAWN)->sum('user_withdrawals.amount');
        //查询用户未提现的总金额
        $list['sum_no_withdrawn_amount'] = $preList->where('status', UserWithdrawal::NO_WITHDRAWAL)->sum('user_withdrawals.amount');
        //返回用户提现数据
        return $this->success($list);
    }

    //完成提现
    public function completeWithdrawal(\App\Http\Requests\User\UserWithdrawal $request)
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
            //修改提现状态为已提现
            UserWithdrawal::where('id', $input['id'])->update([
                'status' => UserWithdrawal::WITHDRAWN
            ]);
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }
}
