<?php

namespace App\Http\Requests\Recruitment;

use App\Http\Requests\BaseFormRequest;

class RecruitmentPosition extends BaseFormRequest
{
    public function rules()
    {
        return [
            'position_type_id' => [
                'exists:recruitment_position_types,id'
            ],
            'min_salary' => [
                'integer',
                'min:0'
            ],
            'max_salary' => [
                'integer',
                'min:0',
                'gte:min_salary'
            ],
            'education_id' => [
                'exists:recruitment_education,id'
            ],
            'experience_id' => [
                'exists:recruitment_experiences,id'
            ],
            'mobile' => [
                'regex:/^1[345789][0-9]{9}$/'
            ],
            'region_code' => [
                'exists:regions,region_code,level,3'
            ],
            'status' => [
                'in:0,1'
            ]
        ];
    }

    public function messages()
    {
        return [
            'max_salary.gte' => __('validation.notLessThanValue1', [
                'value1' => __('validation.attributes.min_salary')
            ]),
        ];
    }
}
