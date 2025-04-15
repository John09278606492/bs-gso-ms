<?php

namespace App\Providers;

use Illuminate\Queue\Worker;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class QueueWorkerProvider extends ServiceProvider
{
    public function boot()
    {
        if (PHP_SAPI !== 'cli') { // Optional: Ensure it only runs in your desktop context
            $this->startQueueWorker();
        }
    }

    protected function startQueueWorker()
    {
        $worker = app(Worker::class);
        $connection = config('queue.default'); // e.g., 'database', 'redis'
        $queue = 'default';

        // Run in a loop (non-blocking if possible)
        while (true) {
            $worker->runNextJob($connection, $queue, new \Illuminate\Queue\WorkerOptions());
            sleep(1); // Prevent CPU overload
        }
    }
}
