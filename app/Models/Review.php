<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'model_id',
        'model_type',
        'content',
        'rate'
    ];

    const TYPE = [
        'App\Nursery' => 'Nursery Review',
        'App\CourseNursery'=> 'Course Review'
    ];
}
