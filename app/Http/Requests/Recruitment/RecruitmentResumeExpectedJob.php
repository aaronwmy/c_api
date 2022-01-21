<?php

namespace App\Http\Requests\Recruitment;

use App\Http\Requests\BaseFormRequest;

class RecruitmentResumeExpectedJob extends BaseFormRequest
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
            'job_type' => [
                'in:1,2'
            ],
            'region_code' => [
                'exists:regions,region_code,level,3'
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
