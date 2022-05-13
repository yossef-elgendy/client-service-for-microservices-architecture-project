<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
        'child_id',
        'nursery_id',
        'start_date',
        'due_date',
        'payment_date',
        'payment_method',
        'status',
        'reservation_id',
      ];

      protected $casts = [
        'payment_method_details' => 'array',
      ];

      const SUBSCRIPTION_STATUS = [
        0 => 'unpaid',
        1 => 'paid'
      ];

      const PAYMENT_METHOD = [
        0 => 'cash',
        1 => 'card',
      ];

      public function reservation()
      {
        return $this->belongsTo(Reservation::class);
      }

      public function child()
      {
        return $this->belongsTo(Child::class);
      }

}
