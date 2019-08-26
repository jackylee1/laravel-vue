<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{

    protected $fillable = ['order_id', 'type', 'status', 'amount', 'inner_transaction_no', 'payment_id', 'paid_at'];
    const TYPE_WECHAT     = 1;
    const TYPE_MAP        = [
        self::TYPE_WECHAT => '微信支付'
    ];
    const STATUS_PENDING  = 1; // 待付款
    const STATUS_PAID     = 2; // 已付款
    const STATUS_FAIL     = 3; // 已失败
    const STATUS_REFUNDED = 4; // 已退款
    const STATUS_MAP      = [
        self::STATUS_PENDING  => '待付款',
        self::STATUS_PAID     => '已付款',
        self::STATUS_FAIL     => '已失败',
        self::STATUS_REFUNDED => '已退款',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public static function makePaymentTransactionNo(Order $order)
    {
        $payBy    = 'WX_';
        $date     = date('ymd');
        $offset   = time() - strtotime($date);
        $timeFact = $offset + explode(" ", microtime())[0] * 1000000;
        mt_srand((int)$timeFact);
        $tail = substr($timeFact + mt_rand(10000000, 99999999), 0, 6);

        return sprintf('%s-%04s-%d-%06s', $payBy, $date, $order->id, $tail);
    }

    /**
     * @param Order $order
     * @return Payment|Model
     */
    public static function makePayment(Order $order)
    {
        $payment = Payment::create([
            'order_id'             => $order->id,
            'type'                 => self::TYPE_WECHAT,
            'status'               => self::STATUS_PENDING,
            'amount'               => $order->amount,
            'inner_transaction_no' => self::makePaymentTransactionNo($order)
        ]);

        return $payment;
    }

    /**
     * 根据生产环境还是 还是测试环境 计算出实际需要支付的价格 方便测试
     *
     * @param int $shrink
     * @return int|mixed
     */
    public function getProductionOrMockPrice(int $shrink = 10000)
    {
        if (\App::environment('production')) {
            return $this->amount;
        } else {
            return intval(max(1, $this->amount / $shrink));
        }
    }
}
