<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\BaseController;
use App\Models\User\User;
use App\Models\User\UserMoneyRecord;
use App\Models\User\UserWithdrawal;
use App\Rules\User\WithdrawalAmountAppliedByUserIsRight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserWithdrawalController extends BaseController
{
    //申请提现
    public function addUserWithdrawal(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'amount' => [
                'required',
                new WithdrawalAmountAppliedByUserIsRight()
            ],
            'user_bank_id' => [
                'required',
                'exists:user_banks,id,user_id,' . User::getCurrentUserCache('id')
            ]
        ]);
        //开启数据库事务
        DB::beginTransaction();
        try {
            //增加用户提现
            UserWithdrawal::create([
                'user_id' => User::getCurrentUserCache('id'),
                'amount' => $input['amount'],
                'user_bank_id' => $input['user_bank_id']
            ]);
            //扣掉用户金钱
            $userInfo = User::where('id', User::getCurrentUserCache('id'))->first();
            $userInfo->money = $userInfo->money - $input['amount'];
            $userInfo->save();
            UserMoneyRecord::create([
                'user_id' => User::getCurrentUserCache('id'),
                'amount' => $input['amount'] * -1,
                'balance' => $userInfo->money,
                'remark' => '用户提现'
            ]);
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
