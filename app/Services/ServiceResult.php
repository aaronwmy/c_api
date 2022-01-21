<?php

namespace App\Services;

class ServiceResult
{
    private $code = null;
    private $message = null;
    private $data = null;

    function __construct($code, $message = '', $data = null)
    {
        $this->setCode($code);
        $this->setMessage($message);
        $this->setData($data);
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }
}
