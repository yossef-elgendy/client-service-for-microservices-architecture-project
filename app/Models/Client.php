<?php

namespace App\Models;


use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Client extends Authenticatable // implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'full_name',
        'email',
        'mobile_number',
        'password',
        'location',
        'payment_info',
        'status',
        'gender'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
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
        'email_verified_at' => 'datetime',
        'location'=>'array',
        'payment_info'=>'array',
    ];


    public function linked_acounts()
    {
        return $this->hasMany(LinkedAcount::class,'client_id','id');
    }

    public function children()
    {
        return $this->hasMany(Child::class,'client_id','id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class,'client_id','id');
    }



    public function mediafile()
    {
        return $this->morphOne(Media::class, 'mediafileable');
    }
}
