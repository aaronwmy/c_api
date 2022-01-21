<?php

namespace App\Services\EncryptionAndDecryption;

class RSA
{
    private $private_key;
    private $public_key;
    private $returnmode;

    function __construct($private_key, $public_key, $returnmode)
    {
        $this->private_key = $private_key;
        $this->public_key = $public_key;
        $this->returnmode = $returnmode;
    }

    //公钥加密
    public function publicencrypt($str)
    {
        openssl_public_encrypt($str, $data, $this->public_key);
        if ($this->returnmode == 'base64') {
            return base64_encode($data);
        } elseif ($this->returnmode == 'dxhex') {
            return strtoupper(bin2hex($data));
        } elseif ($this->returnmode == 'xxhex') {
            return strtolower(bin2hex($data));
        }
    }

    //私钥加密
    public function privateencrypt($str)
    {
        openssl_private_encrypt($str, $data, $this->private_key);
        if ($this->returnmode == 'base64') {
            return base64_encode($data);
        } elseif ($this->returnmode == 'dxhex') {
            return strtoupper(bin2hex($data));
        } elseif ($this->returnmode == 'xxhex') {
            return strtolower(bin2hex($data));
        }
    }

    //私钥解密
    public function privatedecrypt($str)
    {
        if ($this->returnmode == 'base64') {
            openssl_private_decrypt(base64_decode($str), $data, $this->private_key);
        } elseif ($this->returnmode == 'dxhex') {
            openssl_private_decrypt($this->hex2bin(strtolower($str)), $data, $this->private_key);
        } elseif ($this->returnmode == 'xxhex') {
            openssl_private_decrypt($this->hex2bin(strtolower($str)), $data, $this->private_key);
        }
        return $data;
    }

    //公钥解密
    public function publicdecrypt($str)
    {
        if ($this->returnmode == 'base64') {
            openssl_public_decrypt(base64_decode($str), $data, $this->public_key);
        } elseif ($this->returnmode == 'dxhex') {
            openssl_public_decrypt($this->hex2bin(strtolower($str)), $data, $this->public_key);
        } elseif ($this->returnmode == 'xxhex') {
            openssl_public_decrypt($this->hex2bin(strtolower($str)), $data, $this->public_key);
        }
        return $data;
    }

    //生成公钥和私钥
    public static function createkeys()
    {
        $res = openssl_pkey_new(array("private_key_bits" => 2048, "private_key_type" => OPENSSL_KEYTYPE_RSA,));
        openssl_pkey_export($res, $privKey);
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey['key'];
        return array('privatekey' => $privKey, 'publickey' => $pubKey);
    }

    private function hex2bin($hexData)
    {
        $binData = "";
        for ($i = 0; $i < strlen($hexData); $i += 2) {
            $binData .= chr(hexdec(substr($hexData, $i, 2)));
        }
        return $binData;
    }
}
