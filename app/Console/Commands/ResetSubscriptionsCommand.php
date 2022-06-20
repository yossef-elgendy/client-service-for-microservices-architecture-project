<?php

namespace App\Console\Commands;

use App\Jobs\ResetSubscriptionJob;
use Illuminate\Console\Command;

class ResetSubscriptionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:subs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ResetSubscriptionJob::dispatch()
        ->onConnection('rabbitmq')
        ->onQueue(config('queue.rabbitmq_queue.client_service'));
    }
}
