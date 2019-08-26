<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Response\ErrorCode;
use App\Models\AccountStatement;
use Illuminate\Http\Request;

class CommissionController extends Controller
{

    const TTL = 5; // 缓存时间 5 分钟

    /**
     * 我的业绩页对应接口
     * @param Request $request
     * @return CommissionController
     */
    public function index(Request $request)
    {
        $month = $request->get('month', date('Ym'));

        $agent = $request->member->agent;

        if (!$agent) {
            return $this->error(ErrorCode::ERR_EMPTY, '当前用户非代理人');
        }

        // 这几个缓存，会在用户下单的时候清楚掉
        $cacheKey = sprintf(AccountStatement::COMMISSION_CACHE_KEY, $agent->id, $month);

        $statements = \Cache::remember($cacheKey, self::TTL, function () use ($month, $agent) {
            return $agent->statements()->commission($month)->type()->with('order')->latest()->get(); // 只筛选当月佣金部分，不包含提现的流水
        });

        $data['statements'] = $statements; // 当月佣金流水

        $data['mixed'] = [
            'total_commission'   => \Cache::remember(sprintf(AccountStatement::TOTAL_COMMISSION, $agent->id), self::TTL, function () use ($agent) {
                return $agent->statements()->commission()->type()->sum('amount');
            }), // 所有佣金
            'pending_commission' => \Cache::remember(sprintf(AccountStatement::PENDING_COMMISSION, $agent->id), self::TTL, function () use ($agent) {
                return $agent->statements()->commission()->type()->where('status', AccountStatement::STATUS_PENDING)->sum('amount');
            }), // 累计待结算佣金
            'this_month'         => $statements->sum('amount'),
        ];

        $data['meta'] = [
            'type'   => AccountStatement::TYPE_MAP,
            'status' => AccountStatement::STATUS_MAP
        ];

        return $this->success($data);
    }
}
