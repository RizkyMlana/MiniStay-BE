<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $dates = ['user_id','room_id', 'check_in', 'check_out', 'total_price', 'booking_code', 'status', 'qr_code_url'];
    protected $casts = ['check_in'=>'date', 'check_out'=>'date'];
    
    public function user() {
        return $this->belongsTo(User::class);
    }
    public function room(){
        return $this->belongsTo(Room::class);
    }
    public function payments(){
        return $this->hasOne(Payment::class);
    }
    public function review(){
        return $this->hasOne(Review::class);
    }
    public function scan(){
        return $this->hasOne(BookingScan::class);
    }
}
