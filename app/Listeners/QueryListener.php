<?php

namespace App\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Log;

/**
 * SQL日志监听
 * Class QueryListener
 * @package App\Listeners
 */
class QueryListener
{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  QueryExecuted $event
     * @return void
     */
    public function handle(QueryExecuted $event)
    {
        $log = vsprintf(str_replace("?", "'%s'", $event->sql), $event->bindings);
        Log::debug($log);
    }
}
