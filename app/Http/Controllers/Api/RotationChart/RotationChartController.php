<?php

namespace App\Http\Controllers\Api\RotationChart;

use App\Http\Controllers\BaseController;
use App\Models\RotationChart\RotationChart;

class RotationChartController extends BaseController
{
    //获得轮播图数据
    public function getList(\App\Http\Requests\RotationChart\RotationChart $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'place' => [
                'required'
            ]
        ]);
        //查询轮播图数据
        $list = RotationChart::where('place', $input['place'])->orderBy('sort', 'asc')->get();
        return $this->success($list);
    }
}
