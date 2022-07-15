<?php

namespace App\Jobs\ProviderDispatched;

use App\Jobs\ServicesDispatched\UserNotificationSendJob;
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
use Illuminate\Support\Facades\DB;

class ProviderReservationCencelJob implements ShouldQueue
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
        DB::beginTransaction();
         try {
            $reservation = Reservation::findOrFail($this->data['reservation_id']);
            $reservation->update([
                'status' => $this->data['status'],
                'provider_end'=> $this->data['provider_end'] ?? 0,
                'reply' => $this->data['reply']
            ]);

            
            UserNotificationSendJob::dispatch([
                'user_id' => $reservation->client_id,
                'title' => 'Reservation Canceled',
                'type' => 'reservation_cancel',
                'body' => 'Reservation for '.ucfirst($reservation->child->name).' has been canceled by the nursery.
                The nursery replied with: '.$this->data['reply'],
                'data' => [
                  'reservation_id' => $reservation->id,
                  'child_name' => $reservation->child->name,
                  'child_id'=>$reservation->child->id,
                  'nursery_id' => $reservation->nursery_id,
                ],
              ])
                ->onConnection('rabbitmq')
                ->onQueue(config('queue.rabbitmq_queue.api_gateway_service'));


            if(isset($this->data['provider_end']) && $this->data['provider_end'] == 1 ) {
                $reservation->delete();
            }
            DB::commit();
        } catch (Exception $e){
            DB::rollBack();

            $this->fail($e);
        }
    }
}
