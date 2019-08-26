<?php

Route::post('login', 'AuthenticateController@login');

Route::group(['middleware' => ['token', 'user', 'rbac']], function () {

    Route::resource('tag', 'TagController');

    Route::get('upload/policy', 'ProductPosterController@getPolicy');
    Route::get('product/{id}/poster', 'ProductPosterController@posters');
    Route::post('product/poster', 'ProductPosterController@store');
    Route::patch('product/poster/{id}', 'ProductPosterController@update');
    Route::delete('product/poster/{id}', 'ProductPosterController@destroy');

    Route::get('product/category', 'ProductController@category');
    Route::get('product/{id}/pes', 'ProductController@pes'); // 获取产品下所有体检项
    Route::resource('product', 'ProductController');

    Route::post('product/{id}/pe', 'PhysicalExaminationController@store'); // 添加体检项
    Route::patch('product/{id}/pe/{pe_id}', 'PhysicalExaminationController@update'); // 更新体检项
    Route::get('product/{id}/pe_combo', 'PhysicalExaminationController@combo');// 获取产品下所有体检组合
    Route::post('product/{id}/pe_combo', 'PhysicalExaminationController@comboStore');// 添加体检组合码
    Route::delete('product/{id}/pe_combo/{combo_id}', 'PhysicalExaminationController@comboDestroy');// 删除产品下体检组合码

    Route::resource('banner', 'BannerController');

    // 代理人
    Route::get('agent', 'AgentController@index');
    Route::get('agent/tree_data', 'AgentController@tree');
    Route::get('agent/{id}', 'AgentController@show');
    Route::patch('agent/{id}', 'AgentController@update');
    Route::post('agent/import', 'AgentController@import');

    // 提现
    Route::get('withdraw', 'WithdrawController@index');
    Route::post('withdraw/status', 'WithdrawController@status');

    // 用户管理
    Route::get('member', 'MemberController@index');
    Route::get('member/agent/store/{id}', 'MemberController@agentStore');

    // 订单
    Route::get('order', 'OrderController@index');
    Route::get('order/{id}', 'OrderController@show');

    Route::resource('permission', 'PermissionController');
    Route::post('role/{id}/permission', 'RoleController@permission');
    Route::resource('role', 'RoleController');
    Route::post('user/{id}/role', 'UserController@role');
    Route::resource('user', 'UserController');

    Route::group(['prefix' => 'common'], function () {
        Route::post('sms', 'CommonController@sendSms');
    });
});
