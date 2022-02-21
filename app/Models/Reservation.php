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


    const STATUS = [
        1 => "Active",
        0 => "Inactive"
    ];

    protected $cast = [
        'activities' => 'array',
        'courses' => 'array'
    ];
}
