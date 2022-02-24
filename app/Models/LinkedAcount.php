<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkedAcount extends Model
{
    use HasFactory;
    protected $table = 'linked_accounts';


    protected $fillable = [
        'provider',
        'provider_id',
        'client_id',
        'avatar'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
