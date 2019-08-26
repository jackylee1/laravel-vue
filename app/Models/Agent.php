<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{

    protected $fillable = ['member_id', 'open_bank', 'bank_account', 'parent_id'];
    protected $hidden   = ['open_bank', 'bank_account'];
    protected $appends  = ['name'];// 从member表读取

    public static function boot()
    {
        parent::boot();

        // 初始化代理人的账户(开户)
        static::created(function ($model) {
            $model->account()->firstOrCreate([]);
        });
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function parent()
    {
        return $this->belongsTo(Agent::class, 'parent_id');
    }

    /**
     * 代理人下级（即团队）
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function team()
    {
        return $this->hasMany(Agent::class, 'parent_id');
    }

    public function account()
    {
        return $this->hasOne(Account::class);
    }

    public function statements()
    {
        return $this->hasManyThrough(AccountStatement::class, Account::class);
    }

    public function getNameAttribute()
    {
        return Member::where('id', $this->member_id)->value('nick_name'); // 这里不能用 $this->member->nick_name 来获取，不然会在 agent对象上直接返回member对象
    }
}
