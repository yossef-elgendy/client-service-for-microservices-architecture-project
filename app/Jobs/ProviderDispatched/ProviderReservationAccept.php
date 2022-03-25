<?php

namespace App\Jobs\ProviderDispatched;

use App\Models\Client;
use App\Models\Order;
use App\Models\Reservation;
use App\Notifications\Recieved\ReservationInformation;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProviderReservationAccept implements ShouldQueue
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
        $this->data = $data ;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Need {reservation_id, reply, client_id, courses:[{id,cost}, {id,cost}], subscription_fee}

        try {
            $reservation = Reservation::find($this->data['reservation_id']);
            $reservation = $reservation->update([
                'status' => 2,
                'reply' => $this->data['reply']
            ]);

            $totalCost = $this->data['subscription_fee'] ?? 0;
            if(isset($this->data['courses']))
            {
                foreach($this->data['courses'] as $course){
                    $totalCost += $course->cost;
                }
            }


            $order = Order::create([
                'reservation_id'=> $this->data['reservation_id'],
                'totalCost'=>$totalCost,
            ]);

            $client = Client::find($this->data['client_id']);
            
            $client->notify(new ReservationInformation([
                'reservation' => $reservation,
                'order' => $order
            ]));

        } catch (Exception $e){
            echo $e->getMessage();
        }

    }
}
