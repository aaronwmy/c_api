<?php

namespace App\Http\Requests\RotationChart;

use App\Http\Requests\BaseFormRequest;
use App\Rules\File\FileExists;

class RotationChart extends BaseFormRequest
{
    public function rules()
    {
        return [
            'image_url' => [
                new FileExists()
            ],
            'type' => [
                'in:0,1,2,3,4,5'
            ],
            'place' => [
                'in:1,2'
            ],
            'sort' => [
                'integer',
                'min:1'
            ]
        ];
    }
}
