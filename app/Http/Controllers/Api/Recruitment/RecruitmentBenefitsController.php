<?php

namespace App\Http\Controllers\Api\Recruitment;

use App\Http\Controllers\BaseController;
use App\Models\Recruitment\RecruitmentBenefit;

class RecruitmentBenefitsController extends BaseController
{
    //查询招聘福利数据
    public function getList()
    {
        //查询招聘福利数据
        $list = RecruitmentBenefit::get();
        //返回招聘福利数据
        return $this->success($list);
    }
}
