<?php


namespace App\Http\Controllers\Api\CourseOrder\Pay;

use App\Http\Controllers\BaseController;
use App\Models\CourseOrder\CourseOrder;
use App\Rules\CourseOrder\CourseOrderCanBePurchased;
use App\Services\Cache\TempAttributesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Omnipay\Omnipay;

class UnionPayController extends BaseController
{
    //银联支付
    public function pay(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $dataOperator = new TempAttributesService(['orderInfo']);
        $request->validate([
            'order_number' => [
                'required',
                new CourseOrderCanBePurchased($dataOperator)
            ]
        ]);
        //获得能够被购买的订单数据
        $orderInfo = $dataOperator->getOrderInfo();
        //设置银联网关
        $gateway = $this->getGateway((isset($input['is_pc']) && $input['is_pc'] == 1) ? true : false);

        $order = [
            'orderId' => $orderInfo['order_number'],
            'txnTime' => date('YmdHis'),
            'orderDesc' => $orderInfo['course_name'],
            'txnAmt' => $orderInfo['total_amount'] * 100
        ];

        $response = $gateway->purchase($order)->send();

        //返回支付链接
        return $this->success([
            'html' => $response->getRedirectHtml()
        ]);
    }

    //银联app回调
    public function appCallback()
    {
        $this->toCallback(false);
    }

    //银联pc回调
    public function pcCallback()
    {
        $this->toCallback(true);
    }

    //银联回调
    private function toCallback($isPc)
    {
        try {
            //设置银联网关
            $gateway = $this->getGateway($isPc);

            $request = $gateway->completePurchase(['request_params' => $_REQUEST]);
            $response = $request->send();

            //获得参数中的数据
            $payData = $request->getData();
            //如果是成功回调
            if ($response->isPaid()) {
                //遇到成功回调时，更新订单数据，并给教师增加金钱
                //开启数据库事务
                DB::beginTransaction();
                try {
                    //更新订单数据
                    CourseOrder::finishOrder($payData['orderId'], $payData['queryId'], $isPc ? CourseOrder::UNION_PC_PAY : CourseOrder::UNION_APP_PAY);
                    //提交数据库事务
                    DB::commit();
                } catch (\Exception $e) {
                    //数据库事务回滚
                    DB::rollback();
                    //返回失败
                    die('fail');
                }
                //返回成功
                die('success');
            } else {
                //返回失败
                die('fail');
            }
        } catch (\Exception $e) {
            //返回失败
            die('fail');
        }
    }

    //获得银联网关
    private function getGateway($isPc = false)
    {
        $gateway = Omnipay::create('UnionPay_Express');
        $gateway->setEnvironment('sandbox');//设置为沙箱模式
        $gateway->setMerId(env('UNION_PAY_MER_ID'));
        $gateway->setCertId(env('UNION_PAY_CERT_ID'));
        $gateway->setPrivateKey(env('UNION_PAY_PRIVATE_KEY'));
        $gateway->setPublicKey(env('UNION_PAY_PUBLIC_KEY'));
        $gateway->setNotifyUrl($isPc ? env('UNION_PAY_PC_NOTIFY_URL') : env('UNION_PAY_APP_NOTIFY_URL'));
        $gateway->setReturnUrl($isPc ? env('UNION_PAY_PC_RETURN_URL') : env('UNION_PAY_MOBILE_RETURN_URL'));
        return $gateway;
    }
}
