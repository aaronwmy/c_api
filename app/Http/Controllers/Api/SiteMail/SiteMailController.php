<?php

namespace App\Http\Controllers\Api\SiteMail;

use App\Common\Constant;
use App\Http\Controllers\BaseController;
use App\Models\SiteMail\SiteMail;
use App\Models\SiteMail\UserReadSiteMail;
use App\Models\User\User;
use App\Services\WorkerMan\GatewayClientService;
use App\Services\WorkerMan\WorkerManService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiteMailController extends BaseController
{
    //查询系统消息
    public function getSystemMail(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'page' => [
                'integer',
                'min:1'
            ],
            'page_size' => [
                'integer',
                'min:1'
            ]
        ]);
        //设置默认页码
        $page = isset($input['page']) ? addslashes($input['page']) : 1;
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? addslashes($input['page_size']) : 12;
        $where = '';
        if (isset($input['is_read']) && $input['is_read'] == 1) {
            $where = ' and b.mail_id IS NOT null';
        }
        if (isset($input['is_not_read']) && $input['is_not_read'] == 1) {
            $where = ' and b.mail_id IS null';
        }
        //查询站内信数据
        $list = DB::select('SELECT a.*,(CASE WHEN b.mail_id IS null THEN 0 ELSE 1 END) as is_read FROM site_mails a LEFT JOIN (SELECT distinct mail_id FROM user_read_site_mails WHERE user_id=?) b ON a.id=b.mail_id WHERE (a.to_user_id=? OR a.to_user_id=0) ' . $where . ' ORDER BY a.created_at desc limit ?,?', [User::getCurrentUserCache('id'), User::getCurrentUserCache('id'), ($page - 1) * $pageSize, $pageSize]);
        $list = array_map('get_object_vars', $list);
        //返回站内信数据
        return $this->success($list);
    }

    //用户读站内信
    public function readMail(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'mail_id' => [
                'required',
                'exists:site_mails,id'
            ]
        ]);
        //如果操作者已经读过这封站内信，则直接返回成功
        if (UserReadSiteMail::where('mail_id', $input['mail_id'])->where('user_id', User::getCurrentUserCache('id'))->count() > 0) {
            return $this->success();
        }
        //开启数据库事务
        DB::beginTransaction();
        try {
            //增加用户读站内信
            UserReadSiteMail::create([
                'mail_id' => $input['mail_id'],
                'user_id' => User::getCurrentUserCache('id')
            ]);
            //更新用户的未读站内信数量
            $countOfUnreadSiteMail = SiteMail::getSystemMessageCount(User::getCurrentUserCache('id'), SiteMail::IS_NOT_READ);
            User::where('id', User::getCurrentUserCache('id'))->update(['count_of_unread_site_mail' => $countOfUnreadSiteMail]);
            //给用户发送长连接消息
            GatewayClientService::sendToUid(
                Constant::WORKERMAN_API_UID_PREFIX . User::getCurrentUserCache('id'),
                WorkerManService::getSuccessResult(
                    'newSiteMail',
                    ['count_of_unread_site_mail' => $countOfUnreadSiteMail]
                )
            );
            //提交数据库事务
            DB::commit();
        } catch (\Exception $e) {
            //数据库事务回滚
            DB::rollback();
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        return $this->success();
    }
}
