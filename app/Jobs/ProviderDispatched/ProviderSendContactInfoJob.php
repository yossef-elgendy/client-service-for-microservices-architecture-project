<?php

namespace App\Jobs\ProviderDispatched;

use App\Jobs\ServicesDispatched\UserNotificationSendJob;
use App\Models\Client;
use App\Models\Reservation;
use App\Notifications\Recieved\ProviderInfoNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class ProviderSendContactInfoJob implements ShouldQueue
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

            
            // $client = Client::find($reservation->client_id);

            // $client->notify(new ProviderInfoNotification([
            //     'name'=> $this->data['name'],
            //     'location'=> $this->data['location'],
            //     'phone'=> $this->data['phone']
            // ]));

            UserNotificationSendJob::dispatch([
                'user_id' => $reservation->client_id,
                'title' => 'Provider Info',
                'type' => 'provider_info',
                'body' => 'Provider info has been recieved for your child '.ucfirst($reservation->child->name).' rservation.',
                'data' => [
                  'reservation_id'=> $this->data['reservation_id'],
                  'name' => $this->data['name'],
                  'location' => $this->data['location'],
                  'phone' => $this->data['phone'],
                  'location_details' => $this->data['location_details']
                ],
              ])
                ->onConnection('rabbitmq')
                ->onQueue(config('queue.rabbitmq_queue.api_gateway_service'));

           
        } catch (\Exception $e) {
           

            $this->fail($e);
        }
    }
}
