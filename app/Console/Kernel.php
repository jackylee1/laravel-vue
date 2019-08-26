<?php

namespace App\Console;

use App\Jobs\AutoSettleCommissionJob;
use App\Jobs\DummyJob;
use App\Models\Order;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            // 每日结算佣金
            dispatch(new AutoSettleCommissionJob());
        })->daily();

        $schedule->call(function () {
            dispatch((new DummyJob([Order::class, 'timeOutPayHandle']))->setDisplayName('代理人系统：过时未支付订单处理'));
        })->everyFiveMinutes()->name('agent-timeOutPayHandle')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
