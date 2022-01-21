<?php


namespace App\Http\Controllers\Api\CourseOrder;

use App\Http\Controllers\BaseController;
use App\Models\CourseOrder\CourseOrder;
use App\Models\User\User;
use App\Rules\Course\CourseCanBePurchased;
use App\Services\Cache\TempAttributesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends BaseController
{
    //创建订单
    public function createOrder(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $dataOperator = new TempAttributesService(['courseInfo']);
        $request->validate([
            'course_id' => [
                'required',
                new CourseCanBePurchased($dataOperator)
            ]
        ]);
        $courseInfo = $dataOperator->getCourseInfo();
        //查询用户在这个课程下面3秒钟之内未付款的订单
        $orderInfo = CourseOrder::where(
            'user_id',
            User::getCurrentUserCache('id')
        )->where(
            'course_id',
            $input['course_id']
        )->where(
            'status',
            CourseOrder::UNPAID
        )->where([['created_at', '>', date('Y-m-d H:i:s', time() - 3)]])->first();
        //如果用户在这个课程下面3秒钟之内有未付款的订单，则返回这个订单
        if (!empty($orderInfo)) {
            //返回订单号
            return $this->success(['order_number' => $orderInfo['order_number']]);
        }
        //开启数据库事务
        DB::beginTransaction();
        try {
            //将操作者在这个课程下面所有的未支付的订单的状态改成取消
            CourseOrder::where(
                'user_id',
                User::getCurrentUserCache('id')
            )->where(
                'course_id',
                $courseInfo['id']
            )->where('status', CourseOrder::UNPAID)->update(['status' => CourseOrder::CANCELLED]);
            //生成订单号
            $order_number = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
            //创建订单
            $newOrderInfo = CourseOrder::create([
                'user_id' => User::getCurrentUserCache('id'),
                'course_id' => $courseInfo['id'],
                'order_number' => $order_number,
                'total_amount' => $courseInfo['price'],
                'remark' => '购买课程《' . $courseInfo['course_name'] . '》',
            ]);
            //免费课程直接完成订单
            if ($courseInfo['price'] == 0) CourseOrder::where('id', $newOrderInfo['id'])->update([
                'status' => CourseOrder::PAID,
                'pay_time' => date('Y-m-d H:i:s')
            ]);
            //提交数据库事务
            DB::commit();
        } catch (\Exception $e) {
            //数据库事务回滚
            DB::rollback();
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回订单号
        return $this->success(['order_number' => $order_number]);
    }

    //取消订单
    public function cancelOrder(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'id' => [
                'required',
                'exists:course_orders,id,status,' . CourseOrder::UNPAID . ',user_id,' . User::getCurrentUserCache('id')
            ]
        ]);
        try {
            //修改课程订单的状态为已取消
            CourseOrder::where('id', $input['id'])->update([
                'status' => CourseOrder::CANCELLED
            ]);
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //查询用户订单
    public function getList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 12;
        //设置查询条件
        $where = [
            ['course_orders.user_id', User::getCurrentUserCache('id')]
        ];
        if (isset($input['begin_time'])) {
            array_push($where, ['course_orders.created_at', '>=', $input['begin_time']]);
        }
        if (isset($input['end_time'])) {
            array_push($where, ['course_orders.created_at', '<=', $input['end_time']]);
        }
        if (isset($input['status'])) {
            array_push($where, ['course_orders.status', $input['status']]);
        }
        if (isset($input['course_type'])) {
            array_push($where, ['courses.type', $input['course_type']]);
        }
        //查询用户订单数据
        $list = CourseOrder::leftJoin(
            'courses',
            'course_orders.course_id',
            'courses.id'
        )->leftJoin(
            'users',
            'courses.user_id',
            'users.id'
        )->where($where)->select(
            'course_orders.*',
            'courses.course_name',
            'courses.course_cover',
            'courses.type as course_type',
            'courses.user_id as course_user_id',
            'users.nickname',
            'users.portrait'
        )->orderby('course_orders.created_at', 'desc')->paginate($pageSize);
        //返回用户订单数据
        return $this->success($list);
    }

    //查询教师订单
    public function getTeacherOrderList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 12;
        //设置查询条件
        $where = [
            ['courses.user_id', User::getCurrentUserCache('id')]
        ];
        if (isset($input['begin_time'])) {
            array_push($where, ['course_orders.created_at', '>=', $input['begin_time']]);
        }
        if (isset($input['end_time'])) {
            array_push($where, ['course_orders.created_at', '<=', $input['end_time']]);
        }
        if (isset($input['status'])) {
            array_push($where, ['course_orders.status', $input['status']]);
        }
        if (isset($input['course_type'])) {
            array_push($where, ['courses.type', $input['course_type']]);
        }
        //查询用户订单数据
        $list = CourseOrder::leftJoin(
            'courses',
            'course_orders.course_id',
            'courses.id'
        )->leftJoin(
            'users',
            'course_orders.user_id',
            'users.id'
        )->where($where)->select(
            'course_orders.*',
            'courses.course_name',
            'courses.course_cover',
            'courses.type as course_type',
            'users.nickname',
            'users.portrait'
        )->orderby('course_orders.created_at', 'desc')->paginate($pageSize);
        //返回用户订单数据
        return $this->success($list);
    }
}
