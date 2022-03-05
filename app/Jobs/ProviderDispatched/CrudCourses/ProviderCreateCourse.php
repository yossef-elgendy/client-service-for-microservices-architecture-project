<?php

namespace App\Jobs\ProviderDispatched\CrudCourses;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProviderCreateCourse implements ShouldQueue
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
        Course::create($this->data);
        
        // need this data:-
        // [
        //     'id' => $this->data['id'],
        //     'name'=> $this->data['name'],
        //     'nursery_id'=> $this->data['nursery_id'],
        //     'age_range'=>$this->data['age_range'],
        //     'cost'=>$this->data['subscription_fee'],
        //     'rate'=>$this->data['rate'],
        //     'description'=>$this->data['description'],
        // ]
    }
}
