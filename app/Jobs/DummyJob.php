<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DummyJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $pointer;
    protected $params;
    protected $displayName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $pointer, $params = [])
    {
        $this->pointer = $pointer;
        $this->params  = $params;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        call_user_func_array($this->pointer, $this->params);
    }

    public function displayName()
    {
        return self::class . ($this->displayName ? ': ' . $this->displayName : '');
    }

    public function setDisplayName($displayName)
    {
        $this->displayName = (string)$displayName;

        return $this;
    }

    public function getDisplayName()
    {
        return $this->displayName;
    }
}
