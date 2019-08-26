<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Response\ErrorCode;
use App\Models\Account;
use App\Models\AccountStatement;
use App\Models\Withdraw;
use Exception;
use Cache;
use Illuminate\Http\Request;

/**
 * 提现申请
 * Class WithdrawController
 * @package App\Http\Controllers\Admin
 */
class WithdrawController extends Controller
{

    const CACHE_KEY = 'api:wallet:index';

    /**
     * 提现列表
     * @return WithdrawController
     */
    public function index()
    {
        $res['withdraw'] = Withdraw::with('agent')->latest()->paginate();
        $res['options']  = [
            'status' => Withdraw::STATUS_MAP
        ];

        return $this->success($res);
    }

    /**
     * 更新打款状态
     * @param Request $request
     * @return WithdrawController|\Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function status(Request $request)
    {
        $paramData  = $request->all();
        $id         = array_get($paramData, 'id', 0);
        $agent_id   = array_get($paramData, 'agent_id', 0);
        $status     = array_get($paramData, 'status', '');
        $remark     = array_get($paramData, 'remark', '');
        $statusData = null;

        try {
            $result = \DB::transaction(function () use ($id, $agent_id, $status, $remark) {
                $withdraw = Withdraw::where('id', $id)->first();
                if ($withdraw) {
                    switch ($status) {
                        case Withdraw::STATUS_SUCCESS:
                            # 更新提现记录状态
                            $withdraw->status = $status;
                            $withdraw->remark = $remark;
                            $result           = $withdraw->save();
                            break;

                        case Withdraw::STATUS_FAIL:
                            # 更新提现记录状态
                            $withdraw->status = $status;
                            $withdraw->remark = $remark;
                            $result           = $withdraw->save();

                            # 返还提现金额
                            $accounts = Account::where('agent_id', $withdraw->agent_id)->first();
                            $accounts->increment('balance', $withdraw->amount);
                            $accounts->increment('available', $withdraw->amount);

                            # 创建流水信息
                            AccountStatement::create([
                                'account_id'      => $accounts->id,
                                'withdraw_id'     => $withdraw->id,
                                'type'            => AccountStatement::TYPE_REFUND,
                                'amount'          => intval($withdraw->amount),
                                'account_balance' => intval($accounts->available) + intval($withdraw->amount)
                            ]);
                            break;
                        default:
                            break;
                    }

                    if (isset($result)) {
                        // 删除缓存
                        Cache::delete(self::CACHE_KEY . $withdraw->agent_id . date('Ym'));

                        return $result;
                    }

                    throw new Exception('提现记录状态更新失败');
                }

                throw new Exception('提现记录查询失败');
            });

            return $this->success($result);
        } catch (Exception $e) {
            return $this->error(ErrorCode::ERR_SYSTEM, $e->getMessage());
        }
    }
}
