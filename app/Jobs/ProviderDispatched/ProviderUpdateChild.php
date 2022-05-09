<?php

namespace App\Jobs\ProviderDispatched;

use App\Models\Child;
use App\Models\Client;
use App\Notifications\Recieved\ChildUpdatedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class ProviderUpdateChild implements ShouldQueue
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
        try {
            $child = Child::findOrFail($this->data['child_id']);
            $child->update(Arr::except($this->data, ['child_id']));

            $client = Client::findOrFail($child->client_id);
            $client->notify(new ChildUpdatedNotification($child->name, $child->id));

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
