<?php


namespace App\Http\Controllers\Api\CourseOrder\Pay;

use App\Http\Controllers\BaseController;
use App\Models\CourseOrder\CourseOrder;
use App\Rules\CourseOrder\CourseOrderCanBePurchased;
use App\Services\Cache\TempAttributesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Omnipay\Omnipay;

class AlipayController extends BaseController
{
    //支付宝支付
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
        //设置支付宝网关
        $gateway = $this->getGateway((isset($input['is_pc']) && $input['is_pc'] == 1) ? true : false);

        //设置订单内容
        $response = $gateway->purchase()->setBizContent([
            'subject' => $orderInfo['course_name'],
            'out_trade_no' => $orderInfo['order_number'],
            'total_amount' => $orderInfo['total_amount'],
            'product_code' => (isset($input['is_pc']) && $input['is_pc'] == 1) ? 'FAST_INSTANT_TRADE_PAY' : 'QUICK_WAP_PAY',
        ])->send();

        //返回支付链接
        return $this->success([
            'url' => $response->getRedirectUrl()
        ]);
    }

    //支付宝app回调
    public function appCallback()
    {
        $this->toCallback(false);
    }

    //支付宝pc回调
    public function pcCallback()
    {
        $this->toCallback(true);
    }

    //支付宝回调
    private function toCallback($isPc)
    {
        try {
            //设置支付宝网关
            $gateway = $this->getGateway($isPc);
            //验签
            $request = $gateway->completePurchase();

            $request->setParams(array_merge($_POST, $_GET));
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
                    CourseOrder::finishOrder($payData['out_trade_no'], $payData['trade_no'], $isPc ? CourseOrder::ALIPAY_PC_PAY : CourseOrder::ALIPAY_APP_PAY);
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

    //获得支付宝网关
    private function getGateway($isPc = false)
    {
        $gateway = Omnipay::create($isPc ? 'Alipay_AopPage' : 'Alipay_AopWap');
        $gateway->setSignType('RSA2');
        $gateway->setEnvironment('sandbox');//设置为沙箱模式
        $gateway->setAppId(env('ALIPAY_APP_ID'));
        $gateway->setPrivateKey(env('ALIPAY_PRIVATE_KEY'));
        $gateway->setAlipayPublicKey(env('ALIPAY_PUBLIC_KEY'));
        $gateway->setReturnUrl(env($isPc ? 'ALIPAY_PC_RETURN_URL' : 'ALIPAY_MOBILE_RETURN_URL'));
        $gateway->setNotifyUrl($isPc ? env('ALIPAY_PC_NOTIFY_URL') : env('ALIPAY_APP_NOTIFY_URL'));
        return $gateway;
    }
}
