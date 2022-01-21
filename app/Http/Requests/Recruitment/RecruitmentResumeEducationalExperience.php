<?php

namespace App\Http\Requests\Recruitment;

use App\Http\Requests\BaseFormRequest;
use App\Rules\String\YearMonthFormat;

class RecruitmentResumeEducationalExperience extends BaseFormRequest
{
    public function rules()
    {
        return [
            'education_id' => [
                'exists:recruitment_education,id'
            ],
            'admission_year_month' => [
                new YearMonthFormat(),
                function ($attribute, $value, $fail) {
                    //入学年月不能大于当前年月
                    if ((int)date('Ym') < (int)$value) {
                        return $fail(__('validation.notLaterThanCurrentTime'));
                    }
                }
            ],
            'graduation_year_month' => [
                new YearMonthFormat(),
                'gte:admission_year_month'//毕业年月不能小于入学年月
            ]
        ];
    }

    public function messages()
    {
        return [
            'graduation_year_month.gte' => __('validation.notEarlierThanValue1', [
                'value1' => __('validation.attributes.admission_year_month')
            ]),
        ];
    }
}
