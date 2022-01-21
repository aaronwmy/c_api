<?php

namespace App\Http\Controllers\Api\TencentCloudOnDemand;

use App\Http\Controllers\BaseController;
use App\Models\User\User;
use App\Services\TencentCloudOnDemand\SignService;
use Illuminate\Http\Request;

class SignController extends BaseController
{
    //获得腾讯云点播的上传签名
    public function getUploadSign(Request $request)
    {
        //获得参数
        $input = $request->all();
        $request->validate([
            'type' => [
                'required',
                'in:teacher,video'
            ],
            'video_id' => [
                'required_if:type,video'
            ]
        ]);
        if ($input['type'] == 'teacher') {
            $sourceContext = 'teacher_' . User::getCurrentUserCache('id');
            $procedure = SignService::SIMPLE_AES_ENCRYPT_PRESET;
        }
        if ($input['type'] == 'video') {
            $sourceContext = 'video_' . $input['video_id'];
            $procedure = SignService::SIMPLE_AES_ENCRYPT_PRESET;
        }
        return $this->success(['sign' => SignService::getUploadSign($sourceContext, $procedure)]);
    }

    //获得腾讯云点播的播放签名
    public function getPlayVideoSign(Request $request)
    {
        //获得参数
        $input = $request->all();
        $request->validate([
            'file_id' => [
                'required'
            ]
        ]);
        $jwt = SignService::getPlayVideoSign($input['file_id']);
        return $this->success(['sign' => $jwt]);
    }
}
