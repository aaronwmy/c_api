<?php

namespace App\Services\TencentCloudLive;

class UrlService
{
    public static function getPushUrl($streamName, $time)
    {
        $txTime = strtoupper(base_convert(strtotime($time), 10, 16));
        $txSecret = md5(env('TENCENT_CLOUD_LIVE_PUSH_KEY') . $streamName . $txTime);
        $ext_str = "?" . http_build_query(array(
                "txSecret" => $txSecret,
                "txTime" => $txTime
            ));
        return "rtmp://" . env('TENCENT_CLOUD_LIVE_PUSH_DOMAIN') . "/" . env('TENCENT_CLOUD_LIVE_APP_NAME') . "/" . $streamName . (isset($ext_str) ? $ext_str : "");
    }

    public static function getPullUrl($streamName)
    {
        return 'rtmp://' . env('TENCENT_CLOUD_LIVE_PULL_DOMAIN') . '/' . env('TENCENT_CLOUD_LIVE_APP_NAME') . '/' . $streamName;
    }
}
