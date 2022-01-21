<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;
use App\Models\User\School;
use App\Rules\File\FileExists;

class TeacherData extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id_card_number' => [
                'regex:/^[1-9]\d{14}(\d{2}[0-9X])?$/'
            ],
            'id_card_front_pic' => [
                new FileExists()
            ],
            'id_card_back_pic' => [
                new FileExists()
            ],
            'school_user_id' => [
                'exists:schools,user_id,status,' . School::APPROVED
            ]
        ];
    }
}
