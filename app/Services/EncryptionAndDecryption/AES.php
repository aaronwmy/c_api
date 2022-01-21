<?php

namespace App\Services\EncryptionAndDecryption;

class AES
{
    private $key;
    private $mode;
    private $returnmode;
    private $iv;

    /**
     * Aes constructor.
     * @param $key
     */
    function __construct($key, $mode = 1, $returnmode, $iv = '0000000000000000')
    {
        $this->key = $key;
        $this->mode = $mode;
        $this->returnmode = $returnmode;
        $this->iv = $iv;
    }

    /**
     * @param $str
     * @return string
     */
    function encrypt($str)
    {
        if ($this->mode == 'cbc') {
            $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        }
        mcrypt_generic_init($td, $this->key, $this->iv);
        if ($this->mode == 'cbc') {
            $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        }
        $pad = $block - (strlen($str) % $block);
        $str .= str_repeat(chr($pad), $pad);
        $encrypted = mcrypt_generic($td, $str);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        if ($this->returnmode == 'base64') {
            return base64_encode($encrypted);
        } elseif ($this->returnmode == 'dxhex') {
            return strtoupper(bin2hex($encrypted));
        } elseif ($this->returnmode == 'xxhex') {
            return strtolower(bin2hex($encrypted));
        }
    }

    /**
     * @param $code
     * @return bool|string
     */
    function decrypt($code)
    {
        if ($this->mode == 'cbc') {
            $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        }
        mcrypt_generic_init($td, $this->key, $this->iv);
        if ($this->returnmode == 'base64') {
            $str = mdecrypt_generic($td, base64_decode($code));
        } elseif ($this->returnmode == 'dxhex') {
            $str = mdecrypt_generic($td, pack("H*", strtolower($code)));
        } elseif ($this->returnmode == 'xxhex') {
            $str = mdecrypt_generic($td, pack("H*", strtolower($code)));
        }
        if ($this->mode == 'cbc') {
            $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        }
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $this->strIppAdding($str);
    }

    /**
     * For PKCS7 padding
     * @param $string
     * @param int $blockSize
     * @return string
     */
    private function addpadding($string, $blockSize = 16)
    {
        $len = strlen($string);
        $pad = $blockSize - ($len % $blockSize);
        $string .= str_repeat(chr($pad), $pad);
        return $string;
    }

    /**
     * @param $string
     * @return bool|string
     */
    private function strIppAdding($string)
    {
        $sLast = ord(substr($string, -1));
        $slastc = chr($sLast);
        $pCheck = substr($string, -$sLast);
        if (preg_match("/$slastc{" . $sLast . "}/", $string)) {
            $string = substr($string, 0, strlen($string) - $sLast);
            return $string;
        } else {
            return false;
        }
    }

    /**
     * @param $hex
     * @return string
     */
    private function hexToStr($hex)
    {
        $string = '';
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $string;
    }
}
