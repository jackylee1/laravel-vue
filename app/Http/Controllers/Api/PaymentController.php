<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderPaidEvent;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use EasyWeChat\Factory;
use Illuminate\Http\Request;

class PaymentController extends Controller
{

    protected $wechat;

    public function __construct()
    {
        $config       = config('wechat.payment.default');
        $this->wechat = Factory::payment($config);
    }

    public function pay(Request $request, $orderId)
    {
        $member  = $request->member;
        $order   = Order::where('member_id', $member->id)->findOrFail($orderId);
        $payment = Payment::makePayment($order);

        // 1. 首先调用 统一下单 接口，返回 prepay_id
        $result = $this->wechat->order->unify([
            'body'         => $order->title,
            'out_trade_no' => $payment->inner_transaction_no,
            'total_fee'    => $payment->getProductionOrMockPrice(),
            'notify_url'   => route('wechatPayNotify'),
            'trade_type'   => 'JSAPI',
            'openid'       => $member->wechat->open_id
        ]);

        // 2. 生成 jssdk 的支付配置，前端 jssdk 调用 chooseWxPay 唤起微信支付
        $result = $this->wechat->jssdk->sdkConfig($result['prepay_id']);

        return $this->success($result);
    }

    public function wechatPayNotify()
    {
        return $this->wechat->handlePaidNotify(function ($message, $fail) {
            try {
                $payment = Payment::with('order')->where('inner_transaction_no', $message['out_trade_no'])->firstOrFail();

                if (!$payment && !$payment->order) {
                    $fail('订单不存在');
                }

                if ($payment->status == Payment::STATUS_PAID) {
                    // 已经支付成功
                    return true;
                }

                if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                    // 用户是否支付成功
                    if (array_get($message, 'result_code') === 'SUCCESS') {
                        $payment->paid_at     = date('Y-m-d H:i:s');
                        $payment->status      = Payment::STATUS_PAID;
                        $payment->transaction_no = $message['transaction_id']; // 微信支付订单号
                    } elseif (array_get($message, 'result_code') === 'FAIL') {
                        $payment->status = Payment::STATUS_FAIL;
                    }
                } else {
                    return $fail('通信失败，请稍后再通知我');
                }

                \DB::transaction(function () use ($payment) {
                    $payment->save(); //
                    $payment->order->update([
                        'status' => Order::STATUS_PAID,
                    ]);
                });

                event(new OrderPaidEvent($payment->order));

                return true;
            } catch (\Exception $e) {
                // 或者错误消息
                $fail('Order not exists.');
            }
        });
    }
}
