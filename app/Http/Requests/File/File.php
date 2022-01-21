<?php

namespace App\Http\Requests\File;

use App\Http\Requests\BaseFormRequest;

class File extends BaseFormRequest
{
    public function rules()
    {
        return [
            'file' => [
                'required'
            ]
        ];
    }
}
