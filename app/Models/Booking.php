<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'booking_code',
        'user_id',
        'room_id',
        'check_in_date',
        'check_out_date',
        'status',
        'total_price',
        'payment_deadline',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'payment_deadline' => 'datetime',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function room(){
        return $this->belongsTo(Room::class);
    }

    public function rating(){
        return $this->hasOne(Rating::class);
    }
    public function messages(){
        return $this->hasMany(Message::class);
    }
}
