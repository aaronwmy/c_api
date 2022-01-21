<?php

namespace App\Http\Requests\User\Extend;

use App\Http\Requests\User\CompanyData;
use App\Rules\User\CompanyIsNotApproved;

class CompanyDataForApprovedCompany extends CompanyData
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            'user_id' => [
                'integer',
                'min:1',
                new CompanyIsNotApproved()
            ]
        ]);
    }
}
