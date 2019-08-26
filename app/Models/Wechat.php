<?php

namespace App\Models;

use App\Models\Traits\TokenTrait;
use Illuminate\Database\Eloquent\Model;

class Wechat extends Model
{

    use TokenTrait;
    protected $fillable = [
        'member_id',
        'open_id',
        'name',
        'avatar',
        'gender',
        'city',
        'province',
        'country',
    ];
    const GENDER_MALE    = 1;
    const GENDER_FEMALE  = 2;
    const GENDER_UNKNOWN = 3;
    const GENDER_MAP     = [
        self::GENDER_MALE    => '男',
        self::GENDER_FEMALE  => '女',
        self::GENDER_UNKNOWN => '未知',
    ];
    //微信接口返回的男女关系映射
    const WECHAT_GENDER_MAP = [
        0 => self::GENDER_UNKNOWN,
        1 => self::GENDER_MALE,
        2 => self::GENDER_FEMALE,
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
