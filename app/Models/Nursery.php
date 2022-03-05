<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nursery extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'nurseries';

    protected $fillable = [
        'id',
        'name',
        'location',
        'active_hours',
        'status',
        'subscription_fee',
        'rate',
        'social_links'
    ];

     /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'social_links' => 'array',
        'location'=>'array',
        'active_hours'=>'array',
    ];

    const STATUS = [
        1 => "Active",
        0 => "Inactive"
    ];

}
