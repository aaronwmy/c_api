<?php

namespace App\Services\ApiDataAnalysis;

use App\Models\EncryptionAndDecryption\AesKey;
use App\Services\EncryptionAndDecryption\AES;
use App\Services\ServiceResult;

class ApiDataAnalysisService
{
    private static $signKey = '=iVq47i+@atB32A!M~Hwddf&ABM6O)H8++Yjk*bd+cKonHo(Bn7QRx7b+mLJs2(#S%)jQm7E30v5=8MQVtt5U4wI1ED~@fSEq6p)';

    //获得解密后的data
    public static function getData($data, $kbsid)
    {
        //对data参数的base64形式的值进行预处理
        $data = str_replace(' ', '+', str_replace('%2B', '+', urldecode($data)));
        //如果kbsid参数不存在，则返回错误
        if (!$kbsid) return new ServiceResult('001', __('messages.IllegalOperation'));
        //获得aes的密钥和偏置
        $keys = self::getAesKeys($kbsid);
        //如果aes的密钥和偏置不存在，则返回错误
        if (empty($keys)) return new ServiceResult('001', __('messages.IllegalOperation'));
        //对data参数的值进行aes解码
        $Aes = new AES($keys['key'], 'cbc', 'base64', $keys['iv']);
        $data = @$Aes->decrypt($data);
        //将解码data参数得到的字符串转化成参数数组
        $dataArr = explode('&', $data);
        $_data = [];
        foreach ($dataArr as $d) {
            if (strpos($d, '=')) {
                $dArr = explode('=', $d, 2);
                $_data[$dArr[0]] = $dArr[1];
            }
        }
        //验证参数数组中的sign的值
        if (!self::queryVerify($_data)) {
            return new ServiceResult('001', __('messages.IllegalOperation') . '4');
        }
        //将参数数组返回
        return new ServiceResult('000', '', $_data);
    }

    //获得aes的密钥和偏置
    private static function getAesKeys($kbsid)
    {
        $info = AesKey::where('identification_code', $kbsid)->first();
        if (empty($info)) return null;
        return [
            'key' => $info['key'],
            'iv' => $info['iv']
        ];
    }

    //验证sign参数
    private static function queryVerify($data)
    {
        $sign = isset($data['sign']) ? $data['sign'] : '';
        unset($data['sign']);
        ksort($data);
        $str = '';
        foreach ($data as $k => $v) {
            if (empty($str)) $str = $str . $k . '=' . $v;
            else $str = $str . '&' . $k . '=' . $v;
        }
        if ($sign != strtoupper(md5($str . self::$signKey))) {
            return false;
        }
        return true;
    }
}
