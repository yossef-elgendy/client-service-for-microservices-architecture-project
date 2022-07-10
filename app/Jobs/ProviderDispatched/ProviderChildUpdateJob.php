<?php

namespace App\Jobs\ProviderDispatched;

use App\Jobs\ServicesDispatched\UserNotificationSendJob;
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
use Illuminate\Support\Facades\DB;

class ProviderChildUpdateJob implements ShouldQueue
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

            UserNotificationSendJob::dispatch([
                'user_id' => $child->client_id,
                'title' => 'Child Updated',
                'body' => '',
                'data' => [
                  'child_id' => $child->id,
                  'child_name' => $child->name,
                  'nursery_id' => $child->nursery->id,
                  'nursery_name' => $child->nursery->name,
                ],
              ])
                ->onConnection('rabbitmq')
                ->onQueue(config('queue.rabbitmq_queue.api_gateway_service'));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->fail($e);
        }
    }
}
