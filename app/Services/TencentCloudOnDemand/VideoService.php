<?php


namespace App\Services\TencentCloudOnDemand;

use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Vod\V20180717\Models\DeleteMediaRequest;
use TencentCloud\Vod\V20180717\VodClient;

class VideoService
{
    public static function deleteVideo($fileId)
    {
        $cred = new Credential(env('TENCENT_CLOUD_SECRETID'), env('TENCENT_CLOUD_SECRETKEY'));
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint("vod.tencentcloudapi.com");
        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client = new VodClient($cred, "", $clientProfile);
        $req = new DeleteMediaRequest();
        $params = array(
            "FileId" => $fileId,
            "SubAppId" => (int)trim(env('TENCENT_CLOUD_APP_ID'))
        );
        $req->fromJsonString(json_encode($params));
        $client->DeleteMedia($req);
    }
}
