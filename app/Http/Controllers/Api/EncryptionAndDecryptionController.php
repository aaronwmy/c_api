<?php

namespace App\Http\Controllers\Api;

use App\Common\Constant;
use App\Http\Controllers\BaseController;
use App\Models\EncryptionAndDecryption\AesKey;
use App\Services\EncryptionAndDecryption\RSA;
use Illuminate\Http\Request;

class EncryptionAndDecryptionController extends BaseController
{
    public function getDcKey(Request $request)
    {
        //获得参数
        $input = $request->all();
        //用rsa算法对key和iv进行解密
        $RSA = new RSA(env('RSA_PRiVATE_KEY'), '', 'base64');
        $key = $RSA->privatedecrypt(str_replace(' ', '+', str_replace('%2B', '+', $input['key'])));
        $iv = $RSA->privatedecrypt(str_replace(' ', '+', str_replace('%2B', '+', $input['iv'])));
        //如果rsa算法解密失败，则返回错误
        if (empty($key) || empty($iv) || strlen($key) != Constant::AES_KEY_LENGTH || strlen($iv) != Constant::AES_KEY_LENGTH) return $this->error(__('messages.IllegalOperation'));
        try {
            //将key、iv和设备唯一标识码存入数据库
            AesKey::updateOrCreate(['identification_code' => $input['kbsid']],
                [
                    'key' => $key,
                    'iv' => $iv
                ]
            );
        } catch (\Exception $e) {
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功的返回值
        return $this->success();
    }
}
