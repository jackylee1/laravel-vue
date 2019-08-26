<?php

namespace App\Helper;

class MiscHelper
{

    /**
     * 返回是否是生产环境
     *
     * @return bool
     */
    public static function isProductionEnv()
    {
        return config('app.env') === 'production' || config('app.env') === 'stage';
    }
}
