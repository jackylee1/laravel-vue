<?php

Route::post('/shanzhen/order/notify', 'Web\ShanzhenController@orderNotify');
Route::post('/shanzhen/report/notify', 'Web\ShanzhenController@reportNotify');

Route::any('{all}', function () {
    return view('app');
})->where(['all' => '.*']);
