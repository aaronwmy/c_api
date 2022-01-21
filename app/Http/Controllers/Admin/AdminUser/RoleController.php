<?php

namespace App\Http\Controllers\Admin\AdminUser;

use App\Http\Controllers\BaseController;
use App\Models\User\Role;
use App\Rules\Admin\PermissionIdsExists;
use Illuminate\Http\Request;

class RoleController extends BaseController
{
    //查询角色列表
    public function getList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 10;
        //设置查询条件
        $where = [];
        //查询角色数据
        $list = Role::with('permissions')->where($where)->paginate($pageSize);
        //返回角色数据
        return $this->success($list);
    }

    //增加角色
    public function addRole(Request $request)
    {
        //获得参数
        $input = $request->all();
        $request->validate([
            'name' => [
                'required',
                'unique:roles,name,0,id,guard_name,admin'
            ]
        ]);
        try {
            //增加角色
            Role::create(['name' => $input['name'], 'guard_name' => 'admin']);
        } catch (\Exception $e) {
            //数据库事务回滚
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //修改角色
    public function editRole(Request $request)
    {
        //获得参数
        $input = $request->all();
        $request->validate([
            'id' => [
                'required',
                'exists:roles,id'
            ],
            'name' => [
                'required',
            ]
        ]);
        $request->validate([
            'name' => [
                'unique:roles,name,' . $input['id'] . ',id,guard_name,admin'
            ]
        ]);
        try {
            //修改角色
            Role::where('id', $input['id'])->update(['name' => $input['name']]);
        } catch (\Exception $e) {
            //数据库事务回滚
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //删除角色
    public function deleteRole(Request $request)
    {
        //获得参数
        $input = $request->all();
        $request->validate([
            'id' => [
                'required',
                'exists:roles,id',
                'unique:model_has_roles,role_id'
            ]
        ], [
            'id.unique' => __('messages.theRoleIsUsedByTheAdministratorAndCannotBeDeleted')
        ]);
        try {
            //删除角色
            Role::where('id', $input['id'])->delete();
        } catch (\Exception $e) {
            //数据库事务回滚
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //重置多个权限
    public function roleResetPermissions(Request $request)
    {
        //获得参数
        $input = $request->all();
        $request->validate([
            'role_id' => [
                'required',
                'exists:roles,id'
            ],
            'permission_ids' => [
                'required'
            ]
        ]);
        $request->validate([
            'permission_ids' => [
                new PermissionIdsExists(Role::where('id', $input['role_id'])->first('guard_name')['guard_name'])
            ]
        ]);
        try {
            //重置权限
            Role::where('id', $input['role_id'])->first()->syncPermissions(explode(',', $input['permission_ids']));
        } catch (\Exception $e) {
            //数据库事务回滚
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

}
