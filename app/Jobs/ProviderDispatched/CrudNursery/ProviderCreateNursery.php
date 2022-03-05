<?php

namespace App\Jobs\ProviderDispatched\CrudNursery;

use App\Models\Nursery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProviderCreateNursery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $data;

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
        Nursery::create($this->data);
        // data needed:-
        // [
        //     'id' => $this->data['id'],
        //     'name'=> $this->data['name'],
        //     'location'=>$this->data['location'],
        //     'active_hours'=> $this->data['active_hours'],
        //     'status'=> $this->data['status'],
        //     'subscription_fee'=>$this->data['subscription_fee'],
        //     'rate'=>$this->data['rate'],
        //     'social_links'=>$this->data['social_links']
        // ]
    }
}
