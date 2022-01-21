<?php

namespace App\Http\Requests\Recruitment;

use App\Http\Requests\BaseFormRequest;
use App\Rules\File\FileExists;
use App\Rules\String\YearMonthFormat;

class RecruitmentResume extends BaseFormRequest
{
    public function rules()
    {
        return [
            'portrait' => [
                new FileExists()
            ],
            'sex' => [
                'in:1,2'
            ],
            'birth_year_month' => [
                new YearMonthFormat(),
                function ($attribute, $value, $fail) {
                    //出生年月不能大于当前年月
                    if ((int)date('Ym') < (int)$value) {
                        return $fail(__('validation.notLaterThanCurrentTime'));
                    }
                }
            ],
            'first_job_year_month' => [
                new YearMonthFormat(),
                function ($attribute, $value, $fail) {
                    //参加工作年月不能大于当前年月
                    if ((int)date('Ym') < (int)$value) {
                        return $fail(__('validation.notLaterThanCurrentTime'));
                    }
                }
            ],
            'education_id' => [
                'exists:recruitment_education,id'
            ],
            'region_code' => [
                'exists:regions,region_code,level,3'
            ],
            'mobile' => [
                'regex:/^1[345789][0-9]{9}$/'
            ],
            'email' => [
                'email'
            ]
        ];
    }
}
