<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountStatement extends Model
{

    protected $fillable = ['account_id', 'type', 'amount', 'order_id', 'status', 'withdraw_id', 'account_balance'];
    const TYPE_COMMISSION      = 1;
    const TYPE_WITHDRAW        = 2;
    const TYPE_REFUND          = 3;
    const TYPE_TEAM            = 4;
    const TYPE_MAP             = [
        self::TYPE_COMMISSION => '个人佣金',
        self::TYPE_WITHDRAW   => '提现',
        self::TYPE_REFUND     => '提现退还',
        self::TYPE_TEAM       => '团队佣金',
    ];
    const STATUS_PENDING       = 1;
    const STATUS_SETTLED       = 2;
    const STATUS_NULL          = 3;
    const STATUS_MAP           = [
        self::STATUS_PENDING => '待核算',
        self::STATUS_SETTLED => '已核算',
        self::STATUS_NULL    => '无需核算',
    ];
    const COMMISSION_CACHE_KEY = "statements:agent_id:%d:month:%s";
    const TOTAL_COMMISSION     = "total_commission:agent_id:%s";
    const PENDING_COMMISSION   = "pending_commission:agent_id:%s";
    const WALLET_CACHE_KEY     = 'api:wallet:agent_id:%d:month:%s';

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * 时间范围查询
     * @param      $query
     * @param null $month
     * @return mixed
     */
    public function scopeCommission($query, $month = null)
    {
        if ($month) {
            $month = substr($month, 0, 4).'-'.substr($month, 4, 6);
            $query->whereBetween('account_statements.created_at', [date('Y-m-01 00:00:00', strtotime($month)), date('Y-m-d 23:59:59', strtotime("{$month} + 1 month -1 day"))]);
        }
        return $query;
    }

    /**
     * 条件查询
     * @param $query
     * @return mixed
     */
    public function scopeType($query)
    {
        return $query->where('type', self::TYPE_TEAM)->orWhere('type', self::TYPE_COMMISSION);
    }

    /**
     * 提现信息
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function withdraw()
    {
        return $this->belongsTo(Withdraw::class);
    }

    /**
     * 获取订单信息
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
