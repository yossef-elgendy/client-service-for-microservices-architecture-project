<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

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


    protected $casts = [
        'activities' => 'array',
        'courses' => 'array',
    ];

    const RESERVATION_STATUS = [
        '0' => 'not_responded',
        '1' => 'reject',
        '2' => 'accept'
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
}
