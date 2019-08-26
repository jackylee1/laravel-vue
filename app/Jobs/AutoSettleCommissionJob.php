<?php

/**
 * 自动结算订单产品的佣金
 */

namespace App\Jobs;

use App\Models\Account;
use App\Models\AccountStatement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutoSettleCommissionJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @throws \Throwable
     */
    public function handle()
    {
        $time = date('Y-m-d 00:00:00', strtotime('-1 day')); // todo 这里的天数需要改成配置形式

        $statements = AccountStatement::where('status', AccountStatement::STATUS_PENDING)
            ->type()
            ->where('created_at', '<', $time)
            ->get();

        foreach ($statements as $statement) {
            // 结算的时候，只更新"可提现金额"，因为 balance 已经在订单支付成功的时候更新了
            // 相同的， 此时 account_balance 不变
            try {
                \DB::transaction(function () use ($statement) {
                    Account::where('id', $statement->account_id)->increment('available', $statement->amount);

                    $statement->update([
                        'status' => AccountStatement::STATUS_SETTLED
                    ]);
                });
            } catch (\Throwable $e) {
                \Log::critical('佣金结算失败：account_statement_id' . $statement->id . '。错误信息：' . $e->getMessage());
            }
        }
    }
}
