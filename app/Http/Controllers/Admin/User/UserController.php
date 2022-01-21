<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\BaseController;
use App\Http\Requests\User\UserData;
use App\Models\User\User;

class UserController extends BaseController
{
    //查询用户
    public function getUserList(UserData $request)
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
        if (isset($input['sex'])) {
            array_push($where, ['sex', $input['sex']]);
        }
        if (isset($input['type'])) {
            array_push($where, ['type', $input['type']]);
        }
        if (isset($input['begin_time'])) {
            array_push($where, ['created_at', '>=', $input['begin_time']]);
        }
        if (isset($input['end_time'])) {
            array_push($where, ['created_at', '<=', $input['end_time']]);
        }
        //查询用户数据
        $list = User::where($where)->paginate($pageSize);
        //返回用户数据
        return $this->success($list);
    }
}
