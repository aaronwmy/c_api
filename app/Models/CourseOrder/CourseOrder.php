<?php

namespace App\Models\CourseOrder;

use App\Models\BaseModel;
use App\Models\Course\Course;
use App\Models\User\User;
use App\Models\User\UserMoneyRecord;

class CourseOrder extends BaseModel
{
    const PAID = 1;
    const UNPAID = 0;
    const CANCELLED = -1;
    const ALIPAY_APP_PAY = 1;
    const UNION_APP_PAY = 3;
    const ALIPAY_PC_PAY = 4;
    const UNION_PC_PAY = 6;

    //完成订单
    protected function finishOrder($orderNumber, $paymentNumber, $paymentMethod)
    {
        //更新订单数据
        $orderInfo = CourseOrder::where('order_number', $orderNumber)->first();
        if ($orderInfo['status'] == CourseOrder::PAID) return;
        $orderInfo->payment_method = $paymentMethod;
        $orderInfo->payment_number = $paymentNumber;
        $orderInfo->pay_time = date('Y-m-d H:i:s');
        $orderInfo->status = CourseOrder::PAID;
        $orderInfo->save();
        //给教师增加金钱
        $courseInfo = Course::where('id', $orderInfo['course_id'])->first();
        $userInfo = User::where('id', $courseInfo['user_id'])->first();
        $userInfo->money = $userInfo->money + $orderInfo['total_amount'];
        $userInfo->save();
        UserMoneyRecord::create([
            'user_id' => $courseInfo['user_id'],
            'amount' => $orderInfo['total_amount'],
            'balance' => $userInfo->money,
            'remark' => $orderInfo['remark']
        ]);
    }
}
