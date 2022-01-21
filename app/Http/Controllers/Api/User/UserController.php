<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\BaseController;
use App\Http\Requests\User\UserData;
use App\Models\Follow\Follow;
use App\Models\User\User;
use App\Services\DatabaseResponse\DatabaseResponseService;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    //获取用户信息
    public function me()
    {
        return $this->success(User::getCurrentUserCache());
    }

    //修改用户资料
    public function edit(UserData $request)
    {
        //获得参数
        $input = $request->all();
        $updateData = [];
        if (isset($input['portrait'])) {
            $updateData['portrait'] = $input['portrait'];
        }
        if (isset($input['nickname']) && trim($input['nickname']) != '') {
            $updateData['nickname'] = $input['nickname'];
        }
        if (isset($input['sex'])) {
            $updateData['sex'] = $input['sex'];
        }
        try {
            User::where('id', User::getCurrentUserCache('id'))->update($updateData);
        } catch (\Exception $e) {
            return $this->error(__('messages.DatabaseError'));
        }
        return $this->success();
    }

    //查询关注的人
    public function getUserIFollowList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 12;
        //设置查询条件
        $where = [
            ['user_id', User::getCurrentUserCache('id')]
        ];
        //查询关注的人的数据
        $list = Follow::leftJoin(
            'users',
            'follows.to_user_id',
            'users.id'
        )->where($where)->select('users.*')->paginate($pageSize);
        //返回关注的人的数据
        return $this->success($list);
    }

    //查询粉丝
    public function getUserFollowMeList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 12;
        //设置查询条件
        $where = [
            ['to_user_id', User::getCurrentUserCache('id')]
        ];
        //查询粉丝数据
        $list = Follow::leftJoin(
            'users',
            'follows.user_id',
            'users.id'
        )->where($where)->select('users.*')->paginate($pageSize);
        //设置“是否关注”字段
        DatabaseResponseService::preSetIsFollowed('id')->exec($list);
        //返回粉丝数据
        return $this->success($list);
    }
}
