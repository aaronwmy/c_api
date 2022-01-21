<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\BaseController;
use App\Http\Requests\User\UserBank as UserBankRequest;
use App\Models\User\User;
use App\Models\User\UserBank;
use App\Rules\User\UserBankCanBeDeleted;

class UserBankController extends BaseController
{
    //添加用户取款方式
    public function addUserBank(UserBankRequest $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'bank_id' => [
                'required'
            ],
            'fullname' => [
                'required'
            ],
            'branch_address' => [
                'required'
            ],
            'bank_card_number' => [
                'required'
            ],
            'region_code' => [
                'required'
            ]
        ]);
        try {
            //增加用户银行
            UserBank::create([
                'user_id' => User::getCurrentUserCache('id'),
                'bank_id' => $input['bank_id'],
                'fullname' => $input['fullname'],
                'branch_address' => $input['branch_address'],
                'bank_card_number' => $input['bank_card_number'],
                'region_code' => $input['region_code']
            ]);
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //删除用户取款方式
    public function deleteUserBank(UserBankRequest $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'id' => [
                'required',
                new UserBankCanBeDeleted()
            ]
        ]);
        try {
            //删除用户银行
            UserBank::where('id', $input['id'])->delete();
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //查询用户付款方式
    public function getList()
    {
        //查询用户付款方式
        $list = UserBank::leftJoin(
            'banks',
            'user_banks.bank_id',
            'banks.id'
        )->leftJoin(
            'regions',
            'user_banks.region_code',
            'regions.region_code'
        )->where(
            'user_id',
            User::getCurrentUserCache('id')
        )->select('user_banks.*', 'banks.bank_name', 'regions.region_name')->get();
        //返回用户付款方式数据
        return $this->success($list);
    }

}
