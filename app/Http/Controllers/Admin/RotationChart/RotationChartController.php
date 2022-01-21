<?php

namespace App\Http\Controllers\Admin\RotationChart;

use App\Http\Controllers\BaseController;
use App\Http\Requests\RotationChart\Extend\RotationChartForManageRotationCharts;
use App\Models\RotationChart\RotationChart;

class RotationChartController extends BaseController
{
    //查询轮播图
    public function getList(\App\Http\Requests\RotationChart\RotationChart $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 12;
        //设置查询条件
        $where = [];
        if (isset($input['place'])) {
            array_push($where, ['place', $input['place']]);
        }
        //查询轮播图数据
        $list = RotationChart::where($where)->orderBy('sort', 'asc')->paginate($pageSize);
        //返回轮播图数据
        return $this->success($list);
    }

    //添加轮播图
    public function addRotationChart(\App\Http\Requests\RotationChart\RotationChart $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'image_url' => [
                'required'
            ],
            'type' => [
                'required'
            ],
            'place' => [
                'required'
            ],
            'param1' => [
                'required_if:type,1,2,3,4,5'
            ],
            'param2' => [
                'required_if:type,1'
            ]
        ]);
        //获得章的排序值
        $sort = isset($input['sort']) ? $input['sort'] : (RotationChart::getMaxSort($input['place']) + 1);
        try {
            //创建轮播图
            RotationChart::create([
                'image_url' => $input['image_url'],
                'type' => $input['type'],
                'sort' => $sort,
                'place' => $input['place'],
                'param1' => isset($input['param1']) ? $input['param1'] : '',
                'param2' => isset($input['param2']) ? $input['param2'] : '',
                'param3' => isset($input['param3']) ? $input['param3'] : ''
            ]);
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //修改轮播图
    public function editRotationChart(RotationChartForManageRotationCharts $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'id' => [
                'required'
            ],
            'image_url' => [
                'required'
            ],
            'type' => [
                'required'
            ],
            'sort' => [
                'required'
            ],
            'param1' => [
                'required_if:type,1,2,3,4,5'
            ],
            'param2' => [
                'required_if:type,1'
            ]
        ]);
        try {
            //修改轮播图
            RotationChart::where('id', $input['id'])->update([
                'image_url' => $input['image_url'],
                'type' => $input['type'],
                'sort' => $input['sort'],
                'param1' => isset($input['param1']) ? $input['param1'] : '',
                'param2' => isset($input['param2']) ? $input['param2'] : '',
                'param3' => isset($input['param3']) ? $input['param3'] : ''
            ]);
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //删除轮播图
    public function deleteRotationChart(RotationChartForManageRotationCharts $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'id' => [
                'required'
            ]
        ]);
        try {
            //删除轮播图
            RotationChart::where('id', $input['id'])->delete();
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }
}
