<?php

// 微信相关
Route::group(['prefix' => 'wechat'], function () {
    Route::get('jump_gateway', 'WechatController@jumpGateway')->name('wechatJumpGateway');

    Route::middleware(['web', 'wechat.oauth:snsapi_userinfo'])->group(function () {
        Route::get('redirect', 'WechatController@redirect')->name('wechatLoginUserInfo'); // 需要用户授权的微信跳转
    });
    Route::post('config', 'WechatController@config');
});

// 登录
Route::post('login', 'MemberController@login');
Route::delete('logout', 'MemberController@logout');

// 支付异步通知
Route::any('payment/notify/wechat', 'PaymentController@wechatPayNotify')->name('wechatPayNotify'); //微信支付回调通知

// 短信
Route::post('sms', 'CommonController@sendSms');
Route::get('upload_policy', 'CommonController@getUploadPolicy');

// 产品相关接口
Route::get('product', 'ProductController@index');
Route::get('product/{id}', 'ProductController@show');
Route::get('product/{id}/posters', 'ProductController@posters');
Route::get('banner', 'BannerController@index');

// 权益相关
Route::get('pe/address', 'ShanzhenController@addresses'); // 善诊体检机构地址

// 招募相关
Route::get('agent/{id}', 'AgentController@detail'); // 根据代理人的邀请链接参数，获取代理人信息
Route::post('join', 'AgentController@join');

Route::group(['middleware' => ['token', 'member']], function () {

    // 如下接口需要是代理人才能查看
    Route::group(['middleware' => 'agent.check'], function () {
        Route::get('team', 'AgentController@team');
        // 业绩相关
        Route::get('/commission', 'CommissionController@index');

        // 我的钱包
        Route::get('/wallet', 'WalletController@index');
        Route::post('/withdraw', 'WalletController@withdraw');
    });

    Route::resource('address', 'AddressController');
    // 代理人(用户)相关
    Route::post('join', 'AgentController@join');

    Route::post('bind_mobile', 'MemberController@bindMobile');
    Route::post('profile', 'MemberController@update');
    //Route::get('member/profile', 'MemberController@show');
    Route::get('profile', 'AgentController@show'); // 代理人自己的个人资料
    Route::post('identify', 'MemberController@identify'); // 身份验证

    // 订单相关
    Route::get('order', 'OrderController@index');// 我的所有订单
    Route::get('order/{id}/reserve_url', 'ShanZhenController@reserve');// 获取预约地址
    Route::get('order/{id}/report_url', 'ShanZhenController@report');// 获取报告地址

    Route::get('pe_order/{id}', 'OrderController@peOrderDetail');// 体检订单详情

    Route::post('order', 'OrderController@store');// 创建订单
    Route::post('order/{order_id}/pay', 'PaymentController@pay'); // 发起支付
    Route::get('order/{id}', 'OrderController@show');// 订单详情
});
