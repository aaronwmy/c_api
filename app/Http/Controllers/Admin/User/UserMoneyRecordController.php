<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\BaseController;
use App\Models\User\UserMoneyRecord;
use Illuminate\Http\Request;

class UserMoneyRecordController extends BaseController
{
    //查询用户金额变动日志
    public function getList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 10;
        //设置查询条件
        $where = [];
        if (isset($input['mobile'])) {
            array_push($where, ['mobile', 'like', '%' . $input['mobile'] . '%']);
        }
        if (isset($input['nickname'])) {
            array_push($where, ['nickname', 'like', '%' . $input['nickname'] . '%']);
        }
        if (isset($input['begin_time'])) {
            array_push($where, ['user_money_records.created_at', '>=', $input['begin_time']]);
        }
        if (isset($input['end_time'])) {
            array_push($where, ['user_money_records.created_at', '<=', $input['end_time']]);
        }
        if (isset($input['user_id'])) {
            array_push($where, ['user_money_records.user_id', $input['user_id']]);
        }
        //查询用户金额变动数据
        $list = UserMoneyRecord::leftJoin(
            'users',
            'user_money_records.user_id',
            'users.id'
        )->where($where)->select('user_money_records.*', 'users.mobile', 'users.nickname')->paginate($pageSize);
        //返回用户金额变动数据
        return $this->success($list);
    }
}
