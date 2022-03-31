<?php

namespace App\Console\Commands;

use App\Jobs\ServicesDispatched\ServiceRegister;
use Illuminate\Console\Command;

class ApplicationBootCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:boot';

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
        ServiceRegister::dispatch([
            'name' => env('SERVICE_NAME', 'client'),
            'ip_address' => env('SERVICE_IP_ADDRESS', 'client_client'),
            'port' => env('SERVICE_PORT', '8002'),
            'base_uri' => env('APP_URL', 'http://localhost:8002'),
            'secret_token' => env('SECRET_TOKEN', ''),
          ])->onConnection('rabbitmq')->onQueue('api_gateway');
    }
}
