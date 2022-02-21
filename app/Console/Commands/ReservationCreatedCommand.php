<?php

namespace App\Console\Commands;

use App\Jobs\Sent\ReservationCreated;
use Illuminate\Console\Command;

class ReservationCreatedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ping:job';

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
        
        ReservationCreated::dispatch([
            'client_id' => 1,
            'nursery_id' => 1,
            'child_id' => 1,
        ])->onConnection('rabbitmq')->onQueue('client');

    }
}
