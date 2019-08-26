<?php

namespace App\Providers;

use App\Helper\IdentityHelper;
use Illuminate\Support\ServiceProvider;
use Validator;
use Zizaco\Entrust\MigrationCommand;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //扩充身份证验证器
        Validator::extend('identity', function ($attribute, $value, $parameters, $validator) {
            $container = new \Illuminate\Container\Container();
            $container->singleton('IdentityCardHelper', IdentityHelper::class);

            return $container->make('IdentityCardHelper')->isChinaIDCard($value);
        });

        //扩充手机号验证器 验证中国大陆手机号
        Validator::extend('mobile', function ($attribute, $value, $parameters, $validator) {
            $parameters    = $parameters + ['loose']; // 默认宽松模式
            $parameters[0] = 'loose';
            if ($parameters[0] == 'loose') {
                $regx = '/^1[0-9]{10}$/'; //宽松模式：1开头的11位数字
            }

            return boolval(preg_match($regx, $value));
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 解决错误　1071 Specified key was too long; max key length is 767 bytes
        // Laravel 5.4 把默认数据库字符集更改成 utf8mb4，作为对存储 emojis 的支持。只要你运行的是 MySQL v5.7.7 及更高版本，那么你就不会出现本文提到的错误。
        \Schema::defaultStringLength(191);

        // 这个包的兼容问题
        $this->app->extend('command.entrust.migration', function () {
            return new class extends MigrationCommand
            {
                public function handle()
                {
                    parent::fire();
                }
            };
        });
    }
}
