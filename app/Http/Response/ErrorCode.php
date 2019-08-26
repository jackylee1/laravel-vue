<?php
/**
 * @author remember2015@gmail.com
 */

namespace App\Http\Response;

class ErrorCode
{

    const ERR_OK               = 0;
    const ERR_EMPTY            = 1;
    const ERR_PENDING          = 2;
    const ERR_QUERYING         = 3;
    const ERR_NOT_ALLOWED      = 4;
    const ERR_INVALID_ARGUMENT = 5;
    const ERR_SYSTEM           = 6;
    const ERR_DUPLICATE        = 7;
    const ERR_FREQUENCY        = 8;
    const ERR_NOT_LOGIN        = 9;
    const ERR_NEED_REDIRECT    = 10;
    const ERR_VALIDATE         = 11;
    const TEXT_MAP             = [
        self::ERR_OK               => "操作成功",
        self::ERR_EMPTY            => "结果为空",
        self::ERR_PENDING          => "处理中，请稍候",
        self::ERR_QUERYING         => "请稍后查询结果",
        self::ERR_NOT_ALLOWED      => "当前操作未授权",
        self::ERR_INVALID_ARGUMENT => "参数错误",
        self::ERR_SYSTEM           => "系统异常",
        self::ERR_DUPLICATE        => "重复操作",
        self::ERR_FREQUENCY        => "请求过于频繁",
        self::ERR_NOT_LOGIN        => "未登录",
        self::ERR_NEED_REDIRECT    => "需要重定向",
        self::ERR_VALIDATE         => "数据验证错误",
    ];

    public static function getText($code)
    {
        return self::TEXT_MAP[$code] ?? '请求错误';
    }
}
