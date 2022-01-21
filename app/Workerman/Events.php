<?php

namespace App\Workerman;

use App\Common\Constant;
use App\Services\WorkerMan\WorkerManService;
use \GatewayWorker\Lib\Gateway;

class Events
{
    public static function onWorkerStart($businessWorker)
    {
    }

    public static function onConnect($client_id)
    {
    }

    public static function onWebSocketConnect($client_id, $data)
    {
    }

    public static function onMessage($client_id, $message)
    {
        $data = json_decode($message, true);
        if (!empty($data)) {
            if (isset($data['type']) && isset($data['data']) && isset($data['kbsid'])) {
                if ($data['type'] == 'token') {
                    try {
                        $result = json_decode(file_get_contents(env('SCHEME_HOST_PORT') . '/api/user?data=' . $data['data'] . '&kbsid=' . $data['kbsid']), true);
                        if (isset($result['status']) && $result['status'] == 'ok') {
                            Gateway::bindUid($client_id, Constant::WORKERMAN_API_UID_PREFIX . $result['data']['id']);
                            Gateway::sendToCurrentClient(
                                WorkerManService::getSuccessResult(
                                    'token',
                                    ['count_of_unread_site_mail' => $result['data']['count_of_unread_site_mail']]
                                )
                            );
                        } else {
                            Gateway::sendToCurrentClient(WorkerManService::getErrorResult('token', '出现异常错误'));
                        }
                    } catch (\Exception $e) {
                        Gateway::sendToCurrentClient(WorkerManService::getErrorResult('token', '出现异常错误'));
                    }
                } elseif ($data['type'] == 'admin_token') {
                    try {
                        $result = json_decode(file_get_contents(env('SCHEME_HOST_PORT') . '/admin/me?data=' . $data['data'] . '&kbsid=' . $data['kbsid']), true);
                        if (isset($result['status']) && $result['status'] == 'ok') {
                            Gateway::bindUid($client_id, Constant::WORKERMAN_ADMIN_UID_PREFIX . $result['data']['id']);
                            Gateway::sendToCurrentClient(WorkerManService::getSuccessResult('token'));
                        } else {
                            Gateway::sendToCurrentClient(WorkerManService::getErrorResult('token', '出现异常错误'));
                        }
                    } catch (\Exception $e) {
                        Gateway::sendToCurrentClient(WorkerManService::getErrorResult('token', '出现异常错误'));
                    }
                }
            }
        }
    }

    public static function onClose($client_id)
    {
    }
}
