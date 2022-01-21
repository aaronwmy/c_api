<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\BaseController;
use App\Models\User\User;
use App\Models\User\UserMoneyRecord;
use Illuminate\Http\Request;

class UserMoneyRecordController extends BaseController
{
    //查询用户金额变动信息
    public function getList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 12;
        //设置查询条件
        $where = [
            ['user_id', User::getCurrentUserCache('id')]
        ];
        if (isset($input['begin_time'])) {
            array_push($where, ['created_at', '>=', $input['begin_time']]);
        }
        if (isset($input['end_time'])) {
            array_push($where, ['created_at', '<=', $input['end_time']]);
        }
        if (isset($input['year_month'])) {
            $beginTime = $input['year_month'] . '-01 00:00:00';
            $endTime = date('Y-m-t 23:59:59', strtotime($beginTime));
            array_push($where, ['created_at', '>=', $beginTime]);
            array_push($where, ['created_at', '<=', $endTime]);
        }
        //查询用户金额变动数据
        $list = UserMoneyRecord::where($where)->orderBy('created_at', 'desc')->paginate($pageSize);
        //返回用户金额变动数据
        return $this->success($list);
    }
}
