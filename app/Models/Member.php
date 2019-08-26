<?php

namespace App\Models;

use App\Models\Traits\TokenTrait;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{

    use TokenTrait;
    protected $fillable = ['nick_name', 'name', 'mobile', 'identity', 'identity_files', 'valid_date', 'identify_status', 'status'];
    const IDENTIFY_STATUS_PENDING = 1;
    const IDENTIFY_STATUS_APPLY   = 2;
    const IDENTIFY_STATUS_PASSED  = 3;
    const IDENTIFY_STATUS_FAILED  = 4;
    const IDENTIFY_STATUS_MAP     = [
        self::IDENTIFY_STATUS_PENDING => '待提交',
        self::IDENTIFY_STATUS_APPLY   => '待审核',
        self::IDENTIFY_STATUS_PASSED  => '通过审核',
        self::IDENTIFY_STATUS_FAILED  => '审核失败',
    ];
    // status 字段说明
    const STATUS_JOIN_AGENT_NULL     = 0;
    const STATUS_JOIN_AGENT_APPLY    = 1;
    const STATUS_JOIN_AGENT_JOINED   = 2;
    const STATUS_JOIN_AGENT_REJECTED = 3;
    const STATUS_MAP                 = [
        self::STATUS_JOIN_AGENT_NULL     => '',
        self::STATUS_JOIN_AGENT_APPLY    => '申请加入',
        self::STATUS_JOIN_AGENT_JOINED   => '已加入',
        self::STATUS_JOIN_AGENT_REJECTED => '加入申请被拒绝',
    ];
    protected $casts = [
        'identity_files' => 'array'
    ];

    //protected $hidden = ['identity', 'identity_files', 'valid_date', 'identify_status'];

    public function agent()
    {
        return $this->hasOne(Agent::class);
    }

    public function wechat()
    {
        return $this->hasOne(Wechat::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
