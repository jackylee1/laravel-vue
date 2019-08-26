<?php

use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Artisan::command('init_agent', function () {
    // 初始化"星服务代理人"
    if (\App\Models\Member::where('nick_name', '星服务')->first()) {
        return;
    }

    try {
        DB::transaction(function () {
            $member = \App\Models\Member::create([
                'nick_name'       => '星服务',
                'identify_status' => \App\Models\Member::IDENTIFY_STATUS_PASSED
            ]);
            \App\Models\Wechat::create([
                'open_id'   => str_random('16'),
                'name'      => '星服务',
                'avatar'    => 'http://xinglin-image.oss-cn-hangzhou.aliyuncs.com/agent/banner1540348356113',
                'gender'    => \App\Models\Wechat::GENDER_UNKNOWN,
                'country'   => '中国',
                'province'  => '上海',
                'city'      => '上海',
                'member_id' => $member->id
            ]);
            \App\Models\Agent::create([
                'parent_id' => 0,
                'member_id' => $member->id
            ]);
        });
    } catch (Exception $e) {
        echo $e->getMessage();
    }
});
