<?php

namespace App\Http\Controllers\Api\Bank;

use App\Http\Controllers\BaseController;
use App\Models\Bank\Bank;

class BankController extends BaseController
{
    //查询银行
    public function getList()
    {
        //查询银行数据
        $list = Bank::get();
        //返回银行数据
        return $this->success($list);
    }
}
