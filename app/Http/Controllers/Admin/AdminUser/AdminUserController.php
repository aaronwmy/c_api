<?php

namespace App\Http\Controllers\Admin\AdminUser;

use App\Common\Constant;
use App\Http\Controllers\BaseController;
use App\Models\AdminUser\AdminUser;
use App\Rules\Admin\AdminPasswordFormatIsCorrect;
use App\Rules\Admin\AdminPasswordIsRight;
use App\Rules\Admin\RoleIdsExists;
use Illuminate\Http\Request;

class AdminUserController extends BaseController
{
    //查询管理员的信息
    public function getMyInfo()
    {
        $userInfo = AdminUser::getCurrentUserCache();
        return $this->success($userInfo);
    }

    //查询管理员列表
    public function getList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 10;
        //设置查询条件
        $where = [];
        //查询管理员数据
        $list = AdminUser::with('roles')->where($where)->paginate($pageSize);
        //返回管理员数据
        return $this->success($list);
    }

    //增加管理员
    public function addAdminUser(Request $request)
    {
        //获得参数
        $input = $request->all();
        $request->validate([
            'username' => [
                'required',
                'unique:admin_users,username'
            ]
        ]);
        AdminUser::create([
            'username' => $input['username'],
            'password' => password_hash(Constant::DEFAULT_ADMIN_USER_PASSWORD, PASSWORD_BCRYPT)
        ]);
        //返回成功
        return $this->success();
    }

    //修改管理员
    public function editAdminUser(Request $request)
    {
        //获得参数
        $input = $request->all();
        $request->validate([
            'id' => [
                'required',
                'exists:admin_users,id'
            ],
            'username' => [
                'required',
            ],
            'password' => [
                new AdminPasswordFormatIsCorrect()
            ]
        ]);
        $request->validate([
            'username' => [
                'unique:admin_users,username,' . $input['id'] . ',id'
            ]
        ]);
        $updateData = ['username' => $input['username']];
        if (isset($input['password']) && !empty($input['password'])) $updateData['password'] = password_hash($input['password'], PASSWORD_BCRYPT);
        AdminUser::where('id', $input['id'])->update($updateData);
        //返回成功
        return $this->success();
    }

    //重置密码
    public function resetPassword(Request $request)
    {
        //获得参数
        $input = $request->all();
        $request->validate([
            'old_password' => [
                'required',
                new AdminPasswordIsRight(AdminUser::getPassword())
            ],
            'password' => [
                'required',
                new AdminPasswordFormatIsCorrect()
            ]
        ]);
        AdminUser::where('id', AdminUser::getCurrentUserCache('id'))->update([
            'password' => password_hash($input['password'], PASSWORD_BCRYPT)
        ]);
        //返回成功
        return $this->success();
    }

    //重置多个角色
    public function AdminUserResetRoles(Request $request)
    {
        //获得参数
        $input = $request->all();
        $request->validate([
            'role_ids' => [
                'required',
                new RoleIdsExists()
            ],
            'user_id' => [
                'required',
                'exists:admin_users,id'
            ]
        ]);
        AdminUser::where('id', $input['user_id'])->first()->syncRoles(explode(',', $input['role_ids']));
        //返回成功
        return $this->success();
    }

    //修改管理员状态
    public function editStatus(Request $request)
    {
        //获得参数
        $input = $request->all();
        $request->validate([
            'id' => [
                'required',
                'exists:admin_users,id'
            ],
            'status' => [
                'required',
                'in:0,1'
            ]
        ]);
        AdminUser::where('id', $input['id'])->update(['status' => $input['status']]);
        //返回成功
        return $this->success();
    }

}
