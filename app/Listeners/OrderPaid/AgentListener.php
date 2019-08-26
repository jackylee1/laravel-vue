<?php

namespace App\Listeners\OrderPaid;

use App\Events\OrderPaidEvent;

class AgentListener
{

    /**
     * 处理订单权益的生成
     *
     * @param  OrderPaidEvent $event
     * @return void
     */
    public function handle(OrderPaidEvent $event)
    {
        $order = $event->order;
        if (!$agent = $order->agent) {
            // 如果订单不是代理过来的
            return;
        }

        $agent->increment('order_count');
    }
}
