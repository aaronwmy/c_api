<?php

namespace App\Http\Traits;

use App\Common\Constant;
use App\Models\Log\ApiLog;

trait ApiResponse
{
    public function success($data = null)
    {
        $result['status'] = Constant::SUCCESS_STATUS_OF_API_RESPONSE;
        $result['msg'] = '';
        $result['data'] = $data;
        return response()->json($result)->header('Time', date('Y-m-d H:i:s'));
    }

    public function error($msg, $statusCode = Constant::HTTP_OK, $data = null)
    {
        $result['status'] = Constant::ERROR_STATUS_OF_API_RESPONSE;
        $result['msg'] = $msg;
        $result['data'] = $data;
        return response()->json($result, $statusCode)->header('Time', date('Y-m-d H:i:s'));
    }

    private function _unsetNull($arr)
    {
        if ($arr !== null) {
            if (is_array($arr)) {
                if (!empty($arr)) {
                    foreach ($arr as $key => $value) {
                        if ($value === null) {
                            $arr[$key] = '';
                        } else {
                            $arr[$key] = $this->_unsetNull($value);
                        }
                    }
                }
            } else {
                if ($arr === null) {
                    $arr = '';
                }
            }
        } else {
            $arr = '';
        }
        return $arr;
    }
}
