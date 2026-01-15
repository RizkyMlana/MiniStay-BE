<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price_per_day',
        'type',
        'facilities',
        'location'
    ];
    protected $casts = [
        'facilities' => 'array',
    ];
    public function images(){
        return $this->hasMany(RoomImage::class);
    }
    public function bookings(){
        return $this->hasMany(Booking::class);
    }

    public function blocks(){
        return $this->hasMany(RoomBlock::class);
    }

    public function isActive():bool{
        return in_array($this->status, [
            'pending_payment',
            'waiting_confirmation',
            'paid',
        ]);
    }

    public function isPaid():bool{
        return $this->status === 'paid';
    }
}
