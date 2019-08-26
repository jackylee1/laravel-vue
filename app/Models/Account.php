<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{

    protected $fillable = ['agent_id', 'balance', 'available'];

    public function statements()
    {
        return $this->hasMany(AccountStatement::class);
    }
    //
    ///**
    // * 累计佣金（包含了待核算和已核算）
    // * @return mixed
    // */
    //public function totalCommission()
    //{
    //    return $this->statements()->where('type', AccountStatement::TYPE_COMMISSION)->sum('amount');
    //}
    //
    ///**
    // * 带核算佣金
    // * @return mixed
    // */
    //public function pendingCommission()
    //{
    //    return $this->statements()
    //        ->where('type', AccountStatement::TYPE_COMMISSION)
    //        ->where('status', AccountStatement::STATUS_PENDING)
    //        ->sum('amount');
    //}
}
