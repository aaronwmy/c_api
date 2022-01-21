<?php


namespace App\Http\Controllers\Api\User;

use App\Common\Constant;
use App\Http\Controllers\BaseController;
use App\Http\Requests\User\SendSMS;
use App\Services\User\SendSmsService;

class MobileController extends BaseController
{
    //发送短信
    public function sendSMS(SendSMS $request)
    {
        //获得参数
        $input = $request->all();
        $code = SendSmsService::getCode($input['mobile']);
        if (!SendSmsService::send($input['mobile'], $code, Constant::TENCENT_CLOUD_SMS_VERIFICATION_CODE_TEMPLATE_ID)) {
            return $this->error(__('messages.SMSSendingFailed'));
        }

        return $this->success();
    }
}
