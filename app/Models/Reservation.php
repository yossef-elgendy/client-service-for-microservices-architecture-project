<?php

namespace App\Models;

use App\Jobs\ServicesDispatched\UserNotificationSendJob;
use App\Notifications\Recieved\ReservationInformation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User;

class Reservation extends Model
{
    use HasFactory, SoftDeletes, Prunable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'nursery_id',
        'client_id',
        'child_id',
        'provider_end',
        'client_end',
        'activities',
        'courses',
        'status',
        'reservation_start_date',
    ];


    const RESERVATION_TYPE = [
        0 => 'monthly',
        1 => 'daily'
    ];

    protected $casts = [
        'activities' => 'array',
        'courses' => 'array',
    ];

    const RESERVATION_STATUS = [
        '0' => 'not_responded',
        '1' => 'reject',
        '2' => 'accept',
        '3' => 'done'
    ];

    const PROVIDER_END = [
        '0' => 'not_ended',
        '1' => 'ended'
    ];

    const CLIENT_END = [
        '0' => 'not_ended',
        '1' => 'ended'
    ];



    public function child() {
        return $this->belongsTo(Child::class);
    }

    public function prunable()
    {
        return static::where('created_at', '<=', now()->subDays(10));
    }

    protected function pruning()
    {
        $this->update([
            'reply' => "Deleted due to no response."
        ]);

        UserNotificationSendJob::dispatch([
            'user_id' => $this->client_id,
            'title' => 'Reservation Canceled',
            'body' => '',
            'data' => [
              'reservation_id' => $this->id,
              'child_name' => $this->child->name,
              'nursery_id' => $this->nursery_id,
              'reply' => $this->reply
            ],
          ])
            ->onConnection('rabbitmq')
            ->onQueue(config('queue.rabbitmq_queue.api_gateway_service'));
    }

    public function order(){
        return $this->hasOne(Order::class);
    }

     
    public function subscription(){
        return $this->belongsTo(Subscription::class);
    }
}
