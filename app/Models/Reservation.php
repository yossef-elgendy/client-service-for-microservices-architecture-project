<?php

namespace App\Models;

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

        $client = User::find($this->client_id);
        $client->notify(new ReservationInformation($this));
    }

    public function order(){
        return $this->hasOne(Order::class);
    }
}
