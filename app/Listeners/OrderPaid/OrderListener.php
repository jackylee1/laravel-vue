<?php

namespace App\Listeners\OrderPaid;

use App\Events\OrderPaidEvent;
use App\Models\PhysicalExaminationQualification;
use App\Models\Product;

class OrderListener
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

        switch ($order->category) {
            case Product::CATEGORY_PE:
                PhysicalExaminationQualification::createFromOrder($order);
                break;
        }
    }
}
