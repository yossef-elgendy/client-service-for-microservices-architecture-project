<?php

namespace App\Jobs\ProviderDispatched;

use App\Models\Client;
use App\Models\Reservation;
use App\Notifications\Recieved\ReservationInformation;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProviderReservationReject implements ShouldQueue
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
         // Need {reservation_id, reply, client_id}

         try {
            $reservation = Reservation::findOrFail($this->data['reservation_id']);

            $reservation->update([
                'status' => 1,
                'provider_end'=> $this->data['provider_end'] ?? 0,
                'reply' => $this->data['reply']
            ]);

            $client = Client::findOrFail($reservation->client_id);
            $client->notify(new ReservationInformation($reservation));

            if(isset($this->data['provider_end'])) $reservation->delete();

        } catch (Exception $e){
            echo $e->getMessage();
        }
    }
}
