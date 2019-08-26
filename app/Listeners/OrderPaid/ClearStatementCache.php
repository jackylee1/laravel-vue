<?php

namespace App\Listeners\OrderPaid;

use App\Events\OrderPaidEvent;
use App\Models\AccountStatement;

class ClearStatementCache
{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * 清除相关的业绩接口的缓存
     * @param OrderPaidEvent $event
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle(OrderPaidEvent $event)
    {
        $order = $event->order;

        $cacheKeys = [
            sprintf(AccountStatement::COMMISSION_CACHE_KEY, $order->agent->id, date('Ym')),
            sprintf(AccountStatement::TOTAL_COMMISSION, $order->agent->id),
            sprintf(AccountStatement::PENDING_COMMISSION, $order->agent->id),
        ];

        \Cache::deleteMultiple($cacheKeys);
    }
}
