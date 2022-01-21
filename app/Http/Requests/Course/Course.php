<?php

namespace App\Http\Requests\Course;

use App\Http\Requests\BaseFormRequest;
use App\Models\User\User;
use App\Rules\File\FileExists;
use Illuminate\Support\Facades\Storage;

class Course extends BaseFormRequest
{
    public function rules()
    {
        return [
            'price' => [
                'regex:/(^[1-9](\d+)?(\.\d{1,2})?$)|(^0$)|(^\d\.\d{1,2}$)/'
            ],
            'course_cover' => [
                new FileExists()
            ],
            'type' => [
                'in:1,2'
            ]
        ];
    }
}
