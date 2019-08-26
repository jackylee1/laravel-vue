<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    protected $fillable = [
        'member_id',
        'agent_id',
        'title',
        'product_id',
        'category', // 继承自 product
        'amount',
        'commission',
        'status',
        'payment_id'
    ];
    const STATUS_PENDING      = 1; // 待付款
    const STATUS_PAID         = 2; // 付款成功，待处理
    const STATUS_DONE         = 3; // 已处理
    const STATUS_PAY_OVERTIME = 4; // 超时未支付
    const STATUS_TERMINATED   = 5; // 已退单
    const STATUS_MAP          = [
        self::STATUS_PENDING      => '待付款',
        self::STATUS_PAID         => '付款成功',
        self::STATUS_DONE         => '已生效',
        self::STATUS_PAY_OVERTIME => '支付超时',
        self::STATUS_TERMINATED   => '已退单',
    ];
    const PAY_TIMEOUT_MINUTES = 30;

    /**
     * 根据订单类型，返回订单具体内容
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function content()
    {
        switch ($this->category) {
            case Product::CATEGORY_PE:
                return $this->pes();
        }
    }

    public function pes()
    {
        return $this->morphedByMany(PhysicalExamination::class, 'related', 'order_items');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * 创建体检订单
     * @param Member     $member
     * @param Product    $product
     * @param            $physicalExaminationIds
     * @param Agent|null $agent
     * @return mixed
     * @throws \Exception
     */
    public static function makePEOrder(Member $member, Product $product, $physicalExaminationIds, Agent $agent = null)
    {
        $pes     = $product->pes;
        $basicPE = $pes->filter(function ($item) {
            // 基础包
            return $item->type == PhysicalExamination::TYPE_BASIC;
        })->first()->toArray();

        if (!in_array($basicPE['id'], $physicalExaminationIds)) {
            throw new \Exception('必须包含基础包！');
        }

        $diff = array_diff($physicalExaminationIds, $pes->pluck('id')->toArray());

        if ($diff) {
            throw new \Exception('存在产品不包含的体检包！体检包：[' . $pes->pluck('id')->implode(',') . ']');
        }

        try {
            \DB::transaction(function () use ($member, $agent, $product, $physicalExaminationIds, $pes, &$order) {

                $purchasedPEs = $pes->filter(function ($item) use ($physicalExaminationIds) {
                    // 购买的体检包
                    return in_array($item->id, $physicalExaminationIds);
                });
                $totalPrice   = $purchasedPEs->sum('price');

                $order = Order::create([
                    'member_id'  => $member->id,
                    'agent_id'   => $agent ? $agent->id : null,
                    'product_id' => $product->id,
                    'category'   => $product->category,
                    'title'      => $product->name,
                    'amount'     => $totalPrice,
                    'commission' => $product->price * $product->commission_rate, // 佣金按产品价格计算
                    'status'     => Order::STATUS_PENDING
                ]);
                $product->decrement('inventory'); // 下单减库存

                $now = date('Y-m-d H:i:s');

                foreach ($purchasedPEs as $pe) {
                    $orderItems[] = [
                        'order_id'     => $order->id,
                        'related_id'   => $pe->id,
                        'related_type' => $pe->pivot->related_type,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];
                }
                OrderItem::insert($orderItems);
            });
        } catch (\Throwable $e) {
            throw  new \Exception('创建订单失败！');
        }

        return $order;
    }

    public function peOnOrderItems()
    {
    }

    /**
     * 超时未支付订单处理
     * 1. 订单状态修改
     * 2. 产品库存归还
     */
    public static function timeOutPayHandle()
    {
        $timeOutAt     = Carbon::now()->subMinutes(self::PAY_TIMEOUT_MINUTES); // 分钟未支付，作订单失效／放弃处理
        $timeOutOrders = Order::where('status', Order::STATUS_PENDING)
            ->where('created_at', '<', $timeOutAt)
            ->get(['id', 'product_id']);

        if ($timeOutOrders->isNotEmpty()) {
            $oids = $timeOutOrders->pluck('id');
            $pids = $timeOutOrders->pluck('product_id')->toArray();
            $pids = array_count_values($pids);

            \DB::transaction(function () use ($pids, $oids, $timeOutOrders) {
                // 订单状态一次性更新
                Order::whereIn('id', $oids)->delete();
                foreach ($pids as $pid => $num) {
                    // 产品库存，分产品 id　更新
                    Product::where('id', $pid)->increment('inventory', $num);
                }
            });
        }
    }
}
