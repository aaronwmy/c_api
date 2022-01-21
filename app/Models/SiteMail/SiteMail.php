<?php

namespace App\Models\SiteMail;

use App\Common\Constant;
use App\Models\BaseModel;
use App\Models\User\User;
use App\Services\WorkerMan\GatewayClientService;
use App\Services\WorkerMan\WorkerManService;
use Illuminate\Support\Facades\DB;

class SiteMail extends BaseModel
{
    const IS_READ = 1;
    const IS_NOT_READ = 2;

    //给用户发送系统消息
    protected function sendSystemMessageToUser($user_id, $title, $content)
    {
        //创建发送给用户的站内信数据
        SiteMail::create([
            'to_user_id' => $user_id,
            'title' => $title,
            'content' => $content
        ]);
        //更新收到系统消息的用户的未读站内信数量
        $countOfUnreadSiteMail = SiteMail::getSystemMessageCount($user_id, SiteMail::IS_NOT_READ);
        User::where('id', $user_id)->update(['count_of_unread_site_mail' => $countOfUnreadSiteMail]);
        //给用户发送长连接消息
        GatewayClientService::sendToUid(
            Constant::WORKERMAN_API_UID_PREFIX . $user_id,
            WorkerManService::getSuccessResult(
                'newSiteMail',
                ['count_of_unread_site_mail' => $countOfUnreadSiteMail]
            )
        );
    }

    //获得用户系统消息的数量
    protected function getSystemMessageCount($user_id, $status = null)
    {
        $where = '';
        if ($status == self::IS_READ) {
            $where = ' and b.mail_id IS NOT null';
        }
        if ($status == self::IS_NOT_READ) {
            $where = ' and b.mail_id IS null';
        }
        return DB::select('SELECT count(*) as c FROM site_mails a LEFT JOIN (SELECT distinct mail_id FROM user_read_site_mails WHERE user_id=?) b ON a.id=b.mail_id WHERE (a.to_user_id=? OR a.to_user_id=0) ' . $where, [$user_id, $user_id])[0]->c;
    }

}
