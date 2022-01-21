<?php

namespace App\Http\Controllers\Api\Recruitment;

use App\Http\Controllers\BaseController;

class RecruitmentWorkingYearsController extends BaseController
{
    //返回工作年限数据
    public function getList()
    {
        //查询招聘经验数据
        $list = [
            ['title' => '1年以下', 'min_value' => 0, 'max_value' => 1],
            ['title' => '1-2年', 'min_value' => 1, 'max_value' => 2],
            ['title' => '3-5年', 'min_value' => 3, 'max_value' => 5],
            ['title' => '6-7年', 'min_value' => 6, 'max_value' => 7],
            ['title' => '8-10年', 'min_value' => 8, 'max_value' => 10],
            ['title' => '10年以上', 'min_value' => 10, 'max_value' => 0]
        ];
        //返回工作年限数据
        return $this->success($list);
    }
}
