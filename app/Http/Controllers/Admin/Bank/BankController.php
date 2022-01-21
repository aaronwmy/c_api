<?php

namespace App\Http\Controllers\Admin\Bank;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Bank\Bank as BankRequest;
use App\Models\Bank\Bank;

class BankController extends BaseController
{
    //添加银行
    public function addBank(BankRequest $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'bank_name' => [
                'required'
            ],
            'bank_logo' => [
                'required'
            ]
        ]);
        try {
            //添加银行
            Bank::create([
                'bank_name' => $input['bank_name'],
                'bank_logo' => $input['bank_logo']
            ]);
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //修改银行
    public function editBank(BankRequest $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'id' => [
                'required'
            ],
            'bank_name' => [
                'required'
            ],
            'bank_logo' => [
                'required'
            ]
        ]);
        try {
            //修改银行
            Bank::where('id', $input['id'])->update([
                'bank_name' => $input['bank_name'],
                'bank_logo' => $input['bank_logo']
            ]);
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //查询银行
    public function getList()
    {
        //查询银行数据
        $list = Bank::get();
        //返回银行数据
        return $this->success($list);
    }

    //删除银行
    public function deleteBank(BankRequest $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'id' => [
                'required',
                'unique:user_banks,bank_id'
            ]
        ], [
            'id.unique' => __('messages.bankInformationHasBeenUsedAndCannotBeDeleted')
        ]);
        try {
            //删除银行
            Bank::where('id', $input['id'])->delete();
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }
}
