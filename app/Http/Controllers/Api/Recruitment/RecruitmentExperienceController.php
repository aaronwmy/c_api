<?php

namespace App\Http\Controllers\Api\Recruitment;

use App\Http\Controllers\BaseController;
use App\Models\Recruitment\RecruitmentExperience;

class RecruitmentExperienceController extends BaseController
{
    //查询招聘经验数据
    public function getList()
    {
        //查询招聘经验数据
        $list = RecruitmentExperience::get();
        //返回招聘经验数据
        return $this->success($list);
    }
}
