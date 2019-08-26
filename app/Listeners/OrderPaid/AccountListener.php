<?php

namespace App\Listeners\OrderPaid;

use App\Events\OrderPaidEvent;
use App\Models\Account;
use App\Models\AccountStatement;
use App\Models\Product;

class AccountListener
{

    /**
     * 处理订单生成的佣金流水等处理
     *
     * @param  OrderPaidEvent $event
     * @return void
     * @throws \Throwable
     */
    public function handle(OrderPaidEvent $event)
    {
        $order = $event->order;
        if (!$agent = $order->agent) {
            // 如果订单不是代理过来的
            return;
        }

        # 获取产品佣金比例配置
        $product   = Product::find($order->product_id);
        $agentRate = $product->agent_rate;
        $month     = date('Ym');

        # 事务处理
        \DB::transaction(function () use ($order, $agent, $agentRate, $month) {
            if (!$agent->parent) {
                $ownCommission = self::getCommission($order->commission, $agentRate[0][0] ?? 100);    // 自己
            } elseif ($agent->parent && !$agent->parent->parent) {
                $fatherCommission = self::getCommission($order->commission, $agentRate[1][0] ?? 0);    // 父亲
                $ownCommission    = self::getCommission($order->commission, $agentRate[1][1] ?? 100);    // 自己
            } elseif ($agent->parent && $agent->parent->parent) {
                $grandpaCommission = self::getCommission($order->commission, $agentRate[2][0] ?? 0);    // 爷爷
                $fatherCommission  = self::getCommission($order->commission, $agentRate[2][1] ?? 0);    // 父亲
                $ownCommission     = self::getCommission($order->commission, $agentRate[2][2] ?? 100);    // 自己
            } else { // 更多或其他
                $ownCommission = 0;
            }

            if (isset($grandpaCommission)) {
                $agent->parent->parent->account->statements()->create([
                    'type'            => AccountStatement::TYPE_TEAM,
                    'status'          => AccountStatement::STATUS_PENDING, // 待核算（等待账期处理）
                    'order_id'        => $order->id,
                    'amount'          => $grandpaCommission,
                    'account_balance' => $agent->parent->parent->account->balance + $grandpaCommission
                ]);
                Account::where('id', $agent->parent->parent->account->id)->increment('balance', $grandpaCommission);
                static::clearCache($agent->parent->parent->account->agent_id, $month);
            }

            if (isset($fatherCommission)) {
                $agent->parent->account->statements()->create([
                    'type'            => AccountStatement::TYPE_TEAM,
                    'status'          => AccountStatement::STATUS_PENDING, // 待核算（等待账期处理）
                    'order_id'        => $order->id,
                    'amount'          => $fatherCommission,
                    'account_balance' => $agent->parent->account->balance + $fatherCommission
                ]);
                Account::where('id', $agent->parent->account->id)->increment('balance', $fatherCommission);
                static::clearCache($agent->parent->account->agent_id, $month);
            }

            $agent->account->statements()->create([
                'type'            => AccountStatement::TYPE_COMMISSION,
                'status'          => AccountStatement::STATUS_PENDING,      // 待核算（等待账期处理）
                'order_id'        => $order->id,
                'amount'          => $ownCommission,
                'account_balance' => $agent->account->balance + $ownCommission
            ]);
            Account::where('id', $agent->account->id)->increment('balance', $ownCommission);
            static::clearCache($agent->account->agent_id, $month);
        });
    }

    /**
     * 获取佣金
     * @param $commission
     * @param $rate
     * @return float|int
     */
    protected static function getCommission($commission, $rate)
    {
        return $commission * ($rate / 100);
    }

    /**
     * 清除我的钱包/业绩缓存
     * @param $agent_id
     * @param $month
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected static function clearCache($agent_id, $month)
    {
        \Cache::delete(sprintf(AccountStatement::WALLET_CACHE_KEY, $agent_id, $month));
        \Cache::delete(sprintf(AccountStatement::COMMISSION_CACHE_KEY, $agent_id, $month));
        \Cache::delete(sprintf(AccountStatement::TOTAL_COMMISSION, $agent_id));
        \Cache::delete(sprintf(AccountStatement::PENDING_COMMISSION, $agent_id));
    }
}
