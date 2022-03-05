<?php

namespace App\Jobs\ProviderDispatched\CrudActivities;

use App\Models\Activity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProviderDeleteActivity implements ShouldQueue
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
        $this->data = $data ;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // this data is needed
        // ['id']
        $activity = Activity::find($this->data);
        $activity->delete();
    }
}
