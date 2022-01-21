<?php

namespace App\Http\Controllers\Api\Recruitment;

use App\Http\Controllers\BaseController;
use App\Models\Recruitment\RecruitmentEducation;

class RecruitmentEducationController extends BaseController
{
    //查询招聘学历数据
    public function getList()
    {
        //查询招聘学历数据
        $list = RecruitmentEducation::get();
        //返回招聘学历数据
        return $this->success($list);
    }
}
