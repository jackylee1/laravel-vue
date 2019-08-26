<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Response\ErrorCode;
use App\Models\AccountStatement;
use App\Models\Member;
use App\Models\Withdraw;
use Cache;
use Illuminate\Http\Request;

class WalletController extends Controller
{

    const TTL = 5; // 缓存时间 5 分钟

    /**
     * 我的钱包
     * @param Request $request
     * @return WalletController
     */
    public function index(Request $request)
    {
        $month = $request->get('month', date('Ym'));
        $month = substr($month, 0, 4) . '-' . substr($month, 4, 6);
        $agent = $request->member->agent;

        $statements = Cache::remember(sprintf(AccountStatement::WALLET_CACHE_KEY, $agent->id, $month), self::TTL, function () use ($agent, $month) {

            # 处理月份时间
            $startTime = date('Y-m-01 00:00:00', strtotime($month));
            $endTime   = date('Y-m-d 23:59:59', strtotime("{$month} + 1 month -1 day"));

            # 获取流水
            $statements = $agent->statements();
            $statements = $statements->with('withdraw');
            $statements = $statements->with('order');
            $statements = $statements->whereBetween('account_statements.created_at', [$startTime, $endTime]);
            $statements = $statements->orderBy('created_at', 'desc')->get()->toArray();

            return $statements;
        });

        $data = [
            'statements' => $statements,
            'mixed'      => [
                # 所有佣金
                'total_commission'   => $agent->statements()->commission()->type()->sum('amount'),
                # 累计待结算佣金
                'pending_commission' => $agent->statements()->commission()->type()->where('status', AccountStatement::STATUS_PENDING)->sum('amount'),
                # 当前余额
                'this_balance'       => $agent->account->balance,
                'available'          => $agent->account->available
            ],
            'meta'       => [
                'type'          => AccountStatement::TYPE_MAP,
                'status'        => AccountStatement::STATUS_MAP,
                'withdraw_type' => Withdraw::STATUS_MAP
            ]
        ];

        return $this->success($data);
    }

    /**
     * 提现
     * @param Request $request
     * @return WalletController
     * @throws \Throwable
     */
    public function withdraw(Request $request)
    {
        $this->validate($request, [
            'bank_name'    => 'required',
            'bank_account' => 'required',
            'amount'       => 'required|numeric'
        ]);

        $agent = $request->member->agent;

        if ($request->amount <= 0 || $agent->account->available < $request->amount) {
            return $this->error(ErrorCode::ERR_VALIDATE, '可提现金额不足！');
        }
        if ($request->member->identify_status != Member::IDENTIFY_STATUS_PASSED) {
            return $this->error(ErrorCode::ERR_NOT_ALLOWED, '请先进行实名认证！');
        }

        \DB::transaction(function () use ($request, $agent, &$withdraw) {
            // 1. 添加提现记录
            $withdraw = Withdraw::create(array_merge($request->only('bank_name', 'bank_account', 'amount'), [
                'agent_id' => $agent->id,
                'status'   => Withdraw::STATUS_APPLY
            ]));

            // 2. 更新账户金额
            $agent->account->update([
                'balance'   => $agent->account->balance - $withdraw->amount,
                'available' => $agent->account->available - $withdraw->amount,
            ]);

            // 3. 添加账户流水
            $withdraw->accountStatement()->create([
                'account_id'      => $agent->account->id,
                'type'            => AccountStatement::TYPE_WITHDRAW,
                'amount'          => -($withdraw->amount),
                'account_balance' => $agent->account->available - $withdraw->amount
            ]);

            // 4. 删除当月的钱包流水缓存
            Cache::delete(sprintf(AccountStatement::WALLET_CACHE_KEY, $agent->id, date('Y-m')));
        });

        return $this->success($withdraw);
    }
}
