<?php

namespace App\Services\WorkerMan;

use App\Common\Constant;

class WorkerManService
{
    //获得成功返回值
    public static function getSuccessResult($type, $data = '')
    {
        $result['type'] = $type;
        $result['status'] = Constant::SUCCESS_STATUS_OF_API_RESPONSE;
        $result['msg'] = '';
        $result['data'] = $data;
        return json_encode($result);
    }

    //获得失败返回值
    public static function getErrorResult($type, $msg, $data = '')
    {
        $result['type'] = $type;
        $result['status'] = Constant::ERROR_STATUS_OF_API_RESPONSE;
        $result['msg'] = $msg;
        $result['data'] = $data;
        return json_encode($result);
    }
}
