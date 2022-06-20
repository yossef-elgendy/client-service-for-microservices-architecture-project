<?php

namespace App\Jobs;

use App\Jobs\ServicesDispatched\UserNotificationSendJob;
use App\Models\Child;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResetSubscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $current_date = date('Y-m-d H:i:s');
            $subscriptions = Subscription::where('due_date', '<=', $current_date)->update([
                    'start_date' => null,
                    'due_date'=> null,
                    'payment_date'=>null,
                    'status' => 0,
                ]);
            foreach($subscriptions as $subscription){
                $child = Child::find($subscription->child_id);
                $client_id = $child->client->id;
                UserNotificationSendJob::dispatch([
                    'user_id' => $client_id,
                    'title' => 'Subscription Ended',
                    'body' => '',
                    'data' => [
                      'child_id' => $subscription->child->id,
                      'child_name' => $subscription->child->name,
                      'nursery_name' => $subscription->nursery_id,
                      'payment_method' => Subscription::PAYMENT_METHOD[$subscription->payment_method]?? Subscription::PAYMENT_METHOD[0] ,
                      'status' => Subscription::SUBSCRIPTION_STATUS[$subscription->status]??  Subscription::SUBSCRIPTION_STATUS[0],
                    ],
                  ])
                    ->onConnection('rabbitmq')
                    ->onQueue(config('queue.rabbitmq_queue.api_gateway_service'));
            }
        } catch (\Exception $e){
            print_r($e);
        }
        
    }
}
