<?php

namespace App\Http\Requests;

use App\Services\Cache\TempAttributesService;
use Illuminate\Foundation\Http\FormRequest;

class BaseFormRequest extends FormRequest
{
    //数据操作者
    private $dataOperator;
    //数据操作者可以操作的属性名
    protected $attributeNamesOfDataOperator = [];

    public function __construct()
    {
        if (count($this->attributeNamesOfDataOperator) > 0) {
            //创建数据操作者类
            $this->dataOperator = new TempAttributesService($this->attributeNamesOfDataOperator);
        }
    }

    //获得数据操作者
    public function getDataOperator()
    {
        return $this->dataOperator;
    }

    public function authorize()
    {
        return true;
    }

    public function attributes()
    {
        return [];
    }

    public function messages()
    {
        return [];
    }
}
