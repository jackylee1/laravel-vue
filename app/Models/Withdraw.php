<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdraw extends Model
{

    protected $fillable = ['agent_id', 'amount', 'status', 'bank_name', 'bank_account', 'remark', 'finished_at'];
    const STATUS_APPLY   = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_FAIL    = 3;
    const STATUS_MAP     = [
        self::STATUS_APPLY   => '发起提现',
        self::STATUS_SUCCESS => '提现成功',
        self::STATUS_FAIL    => '提现失败',
    ];

    public function accountStatement()
    {
        return $this->hasOne(AccountStatement::class);
    }

    /**
     * 代理人信息
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
