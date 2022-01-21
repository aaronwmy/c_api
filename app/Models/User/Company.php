<?php

namespace App\Models\User;

use App\Models\BaseModel;
use App\Models\Recruitment\RecruitmentPosition;

class Company extends BaseModel
{
    const REJECTED = -1;
    const UNDER_REVIEW = 0;
    const APPROVED = 1;

    protected $primaryKey = 'user_id';

    protected function updatePositionCount($user_id)
    {
        Company::where('user_id', $user_id)->update([
            'position_on_shelf_count' => RecruitmentPosition::where(
                'user_id', $user_id
            )->where('status', RecruitmentPosition::ON_THE_SHELF)->count()
        ]);
    }
}
