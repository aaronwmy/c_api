<?php

namespace App\Http\Controllers\Api\Follow;

use App\Http\Controllers\BaseController;
use App\Models\Follow\Follow;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FollowController extends BaseController
{
    //增加关注
    public function addFollow(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'to_user_id' => [
                'required',
                'exists:users,id,type,!1',
                'unique:follows,to_user_id,0,id,user_id,' . User::getCurrentUserCache('id')
            ]
        ]);
        //开启数据库事务
        DB::beginTransaction();
        try {
            //增加关注
            Follow::create([
                'user_id' => User::getCurrentUserCache('id'),
                'to_user_id' => $input['to_user_id']
            ]);
            //更新用户的关注数量
            User::updateUserFollowCount(User::getCurrentUserCache('id'));
            //更新被关注者的粉丝数量
            User::updateFollowUserCount($input['to_user_id']);
            //提交数据库事务
            DB::commit();
        } catch (\Exception $e) {
            //数据库事务回滚
            DB::rollback();
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //取消关注
    public function deleteFollow(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'to_user_id' => [
                'required',
                'exists:follows,to_user_id,user_id,' . User::getCurrentUserCache('id')
            ]
        ]);
        //开启数据库事务
        DB::beginTransaction();
        try {
            //删除关注
            Follow::where('user_id', User::getCurrentUserCache('id'))->where('to_user_id', $input['to_user_id'])->delete();
            //更新用户的关注数量
            User::updateUserFollowCount(User::getCurrentUserCache('id'));
            //更新被关注者的粉丝数量
            User::updateFollowUserCount($input['to_user_id']);
            //提交数据库事务
            DB::commit();
        } catch (\Exception $e) {
            //数据库事务回滚
            DB::rollback();
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }
}
