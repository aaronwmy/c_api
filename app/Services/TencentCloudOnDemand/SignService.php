<?php


namespace App\Services\TencentCloudOnDemand;


use App\Models\User\User;
use App\Services\EncryptionAndDecryption\AES;
use \Firebase\JWT\JWT;

class SignService
{
    const SIMPLE_AES_ENCRYPT_PRESET = 'SimpleAesEncryptPreset';
    const LONG_VIDEO_PRESET = 'LongVideoPreset';
    const TEACHER_VIDEO_PRESET = 'TeacherVideoPreset';
    const BASIC_DRM_PRESET = 'basicDrmPreset';

    public static function getUploadSign($sourceContext, $procedure)
    {
        $secret_id = env('TENCENT_CLOUD_SECRETID');
        $secret_key = env('TENCENT_CLOUD_SECRETKEY');
        $current = time();
        $expired = $current + 86400;  // 签名有效期：1天
        $Aes = new AES(env('TENCENT_CLOUD_AES_KEY'), 'cbc', 'base64', env('TENCENT_CLOUD_AES_IV'));
        $sourceContext = @$Aes->encrypt($sourceContext);
        $arg_list = array(
            'vodSubAppId' => (int)env('TENCENT_CLOUD_APP_ID'),
            "oneTimeValid" => 1,
            "secretId" => $secret_id,
            "currentTimeStamp" => $current,
            "expireTime" => $expired,
            "random" => rand(),
            'procedure' => $procedure,
            'sourceContext' => $sourceContext
        );
        // 计算签名
        $original = http_build_query($arg_list);
        return base64_encode(hash_hmac('SHA1', $original, $secret_key, true) . $original);
    }

    public static function getPlayVideoSign($fileId)
    {
        $appId = (int)env('TENCENT_CLOUD_APP_ID');
        $currentTime = time();
        $psignExpire = $currentTime + 3600;
        $urlTimeExpire = dechex($psignExpire);
        $key = env('TENCENT_CLOUD_ON_DEMAND_ANTI_THEFT_CHAIN_KEY');

        $payload = array(
            "appId" => $appId,
            "fileId" => $fileId,
            "currentTimeStamp" => $currentTime,
            "expireTimeStamp" => $psignExpire,
            "urlAccessInfo" => array(
                "t" => $urlTimeExpire
            ),
            'pcfg' => self::BASIC_DRM_PRESET
        );

        return JWT::encode($payload, $key, 'HS256');
    }
}
