<?php

namespace App\Http\Controllers\Api\Version;

use App\Http\Controllers\BaseController;

class VersionController extends BaseController
{
    //获得版本
    public function getVersion()
    {
        return $this->success([
            'version_num' => env('VERSION_NUM'),
            'version' => env('VERSION'),
            'msg' => env('VERSION_MSG'),
            'url' => env('APK_DOWNLOAD_URL')
        ]);
    }
}
