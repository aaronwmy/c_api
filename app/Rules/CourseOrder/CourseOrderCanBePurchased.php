<?php

namespace App\Rules\CourseOrder;

use App\Models\Course\Course;
use App\Models\CourseOrder\CourseOrder;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Rule;

class CourseOrderCanBePurchased implements Rule
{
    private $msg;
    private $dataOperator;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($dataOperator)
    {
        $this->dataOperator = $dataOperator;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //查询属于操作者的未购买的已审核的课程的订单信息
        $orderInfo = CourseOrder::leftJoin(
            'courses',
            'course_orders.course_id',
            'courses.id'
        )->where(
            'order_number',
            $value
        )->where(
            'course_orders.status',
            CourseOrder::UNPAID
        )->where(
            'course_orders.user_id',
            User::getCurrentUserCache('id')
        )->where('courses.status', Course::APPROVED)->select('course_orders.*', 'courses.course_name')->first();
        $this->dataOperator->setOrderInfo($orderInfo);
        //如果属于操作者的未购买的已审核的课程的订单信息不存在，则不允许操作
        if (empty($orderInfo)) {
            $this->msg = __('validation.exists');
            return false;
        }
        //如果用户已经购买过该课程，则不允许操作
        if (CourseOrder::where(
                'user_id',
                User::getCurrentUserCache('id')
            )->where(
                'course_id',
                $orderInfo['course_id']
            )->where('status', CourseOrder::PAID)->count() > 0) {
            $this->msg = __('messages.youHaveAlreadyPurchasedCourseAndCannotPurchaseAgain');
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->msg;
    }
}
