<?php

namespace App\Http\Requests\Recruitment;

use App\Http\Requests\BaseFormRequest;
use App\Rules\String\YearMonthFormat;

class RecruitmentResumeJobExperience extends BaseFormRequest
{
    public function rules()
    {
        return [
            'position_type_id' => [
                'exists:recruitment_position_types,id'
            ],
            'entry_year_month' => [
                new YearMonthFormat(),
                function ($attribute, $value, $fail) {
                    //入职年月不能大于当前年月
                    if ((int)date('Ym') < (int)$value) {
                        return $fail(__('validation.notLaterThanCurrentTime'));
                    }
                }
            ],
            'departure_year_month' => [
                new YearMonthFormat(true),
                function ($attribute, $value, $fail) {
                    if ($value != 0) {
                        //离职年月不能大于当前年月
                        if ((int)date('Ym') < (int)$value) {
                            return $fail(__('validation.notLaterThanCurrentTime'));
                        }
                        $entryYearMonth = (int)$this->input('entry_year_month', 0);
                        //入职年月不能大于离职年月
                        if ((int)$entryYearMonth > (int)$value) {
                            return $fail(__('validation.notEarlierThanValue1', [
                                'value1' => __('validation.attributes.entry_year_month')
                            ]));
                        }
                    }
                }
            ],
            'is_shield_resume' => [
                'in:0,1'
            ]
        ];
    }
}
