<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'totalCost',
        'paymob_order_id',
        'payment_key',
        'reservation_id',
        'status'
    ];

    const STATUS = [
        0 => 'Unpaid',
        1 => 'Paid',
        2 => 'Refunded'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
