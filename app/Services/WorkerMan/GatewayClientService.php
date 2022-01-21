<?php

namespace App\Services\WorkerMan;

use GatewayClient\Gateway;

class GatewayClientService
{
    //给某个uid的客户端发送消息
    public static function sendToUid($user_id, $msg)
    {
        Gateway::$registerAddress = '127.0.0.1:' . env('WORKERMAN_REGISTER_PORT');
        Gateway::sendToUid($user_id, $msg);
    }
}
