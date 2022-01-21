<?php

namespace App\Http\Controllers\Admin\AdminUser;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends BaseController
{
    //查询权限列表
    public function getList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 10;
        //设置查询条件
        $where = [];
        //查询权限数据
        $list = Permission::where($where)->paginate($pageSize);
        //返回权限数据
        return $this->success($list);
    }

}
