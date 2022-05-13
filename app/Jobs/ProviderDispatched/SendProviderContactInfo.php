<?php

namespace App\Jobs\ProviderDispatched;

use App\Models\Client;
use App\Models\Reservation;
use App\Notifications\Recieved\ProviderInfoNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendProviderContactInfo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{

            $reservation = Reservation::find($this->data['reservation_id']);
            $client = Client::find($reservation->client_id);

            $client->notify(new ProviderInfoNotification([
                'name'=> $this->data['name'],
                'location'=> $this->data['location'],
                'phone'=> $this->data['phone']
            ]));
            
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
