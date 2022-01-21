<?php

namespace App\Http\Controllers\Admin\CourseOrder;

use App\Http\Controllers\BaseController;
use App\Models\CourseOrder\CourseOrder;
use Illuminate\Http\Request;

class OrderController extends BaseController
{
    //查询订单
    public function getList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 12;
        //设置查询条件
        $where = [];
        if (isset($input['mobile'])) {
            array_push($where, ['mobile', 'like', '%' . $input['mobile'] . '%']);
        }
        if (isset($input['nickname'])) {
            array_push($where, ['nickname', 'like', '%' . $input['nickname'] . '%']);
        }
        if (isset($input['status'])) {
            array_push($where, ['course_orders.status', $input['status']]);
        }
        if (isset($input['begin_time'])) {
            array_push($where, ['course_orders.created_at', '>=', $input['begin_time']]);
        }
        if (isset($input['end_time'])) {
            array_push($where, ['course_orders.created_at', '<=', $input['end_time']]);
        }
        if (isset($input['pay_begin_time'])) {
            array_push($where, ['course_orders.pay_time', '>=', $input['pay_begin_time']]);
        }
        if (isset($input['pay_end_time'])) {
            array_push($where, ['course_orders.pay_time', '<=', $input['pay_end_time']]);
        }
        if (isset($input['user_id'])) {
            array_push($where, ['course_orders.user_id', $input['user_id']]);
        }
        $preList = CourseOrder::leftJoin(
            'users',
            'course_orders.user_id',
            'users.id'
        )->leftJoin(
            'courses',
            'course_orders.course_id',
            'courses.id'
        )->where($where);
        //查询课程订单数据
        $list = (clone $preList)->select(
            'course_orders.*',
            'users.mobile',
            'users.nickname',
            'courses.course_name'
        )->paginate($pageSize)->toArray();
        //查询课程订单的总支付金额
        $list['sum_paid_amount'] = $preList->where('course_orders.status', CourseOrder::PAID)->sum('course_orders.total_amount');
        //返回课程订单数据
        return $this->success($list);
    }
}
