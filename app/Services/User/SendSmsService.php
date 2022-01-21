<?php


namespace App\Services\User;


use App\Common\Constant;
use App\Models\User\MobileCode;
use Carbon\Carbon;
use Overtrue\EasySms\EasySms;

class SendSmsService
{
    //发送短信
    public static function send($phoneNumbers, $code, $templateId)
    {
        $config = config('easysms');
        $easySms = new EasySms($config);

        try {
            $result = $easySms->send($phoneNumbers, [
                'template' => $templateId,
                'data' => [
                    'code' => $code,
                    'time' => Constant::EFFECTIVE_MINUTES_OF_SMS
                ],
            ]);

            if ($result['qcloud']['result']['errmsg'] == 'OK') {
                return true;
            }
        } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
            return false;
        }
        return false;
    }

    //生成短信验证码
    public static function getCode($mobile)
    {
        $codeInfo = MobileCode::where('mobile', $mobile)->where('created_at', '>=', date('Y-m-d H:i:s', time() - Constant::EFFECTIVE_MINUTES_OF_SMS * 60))->first();
        if (empty($codeInfo)) {
            $code = mt_Rand(100000, 999999);
            MobileCode::create([
                'mobile' => $mobile,
                'code' => $code
            ]);
            return $code;
        }
        return $codeInfo['code'];
    }
}
