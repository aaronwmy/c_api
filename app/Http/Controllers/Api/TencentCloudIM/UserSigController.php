<?php

namespace App\Http\Controllers\Api\TencentCloudIM;

use App\Http\Controllers\BaseController;
use App\Models\User\User;
use App\Services\TencentCloudIM\UserSigService;

class UserSigController extends BaseController
{
    //获得腾讯云IM的UserSig
    public function getUserSig()
    {
        $UserSigService = new UserSigService();
        return $this->success($UserSigService->genSig(User::getCurrentUserCache('id')));
    }
}
