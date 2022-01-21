<?php

namespace App\Http\Requests\Course;

use App\Http\Requests\BaseFormRequest;
use App\Models\User\User;

class CourseChapter extends BaseFormRequest
{
    public function rules()
    {
        return [
            'sort' => [
                'integer',
                'min:1'
            ]
        ];
    }
}
