<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Child extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'children';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $fillable = [
        'full_name',
        'nursery_id',
        'client_id',
        'age',
        'time_table',
        'status',
        'gender',
        'issues',
        'marks',
        'rate'
    ];

    const STATUS = [
        1 => "Active",
        0 => "Inactive"
    ];

    const GENDER = [
        0 => "male",
        1 => "female"
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'marks'=>'array',
        'time_table'=>'array',
    ];

}
