<?php

namespace App\Services\String;

use App\Common\Constant;
use Illuminate\Support\Facades\Storage;

class StringService
{
    //手机号加星号
    public static function hideStarMobile($str)
    {
        return substr_replace($str, '****', 3, 4);
    }

    //邮箱加星号
    public static function hideStarEmail($str)
    {
        $email_array = explode("@", $str);
        $prevfix = (strlen($email_array[0]) < 4) ? "" : substr($str, 0, 3);
        $str = preg_replace('/([\d\w+_-]{0,100})@/', '***@', $str);
        return $prevfix . $str;
    }

    //判断是不是base64编码
    public static function checkStringIsBase64($str)
    {
        return $str == base64_encode(base64_decode($str)) ? true : false;
    }

}
