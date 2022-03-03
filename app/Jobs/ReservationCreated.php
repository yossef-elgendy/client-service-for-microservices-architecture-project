<?php

namespace App\Jobs;

use App\Models\Child;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReservationCreated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;

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
        try {
            $child = Child::find($this->data['child_id']);

            return [
                'reservation_id' => $this->data['id'],
                'client_id'=>$this->data['client_id'],
                'nursery_id'=>$this->data['nursery_id'],
                'child_id'=> $child->id,
                'provider_end'=>$this->data['provider_end'],
                'client_end'=>$this->data['client_end'],
                'status'=>$this->data['status'],
                'courses'=> $this->data['courses'],
                'activities'=> $this->data['activities'],
                'name'=> $child->name,
                'age'=>$child->age,
                'gender'=> $child->gender,
                'rate'=> $child->rate,
            ];

        } catch (Exception $e) {
            return $e->getMessage();
        }

    }
}
