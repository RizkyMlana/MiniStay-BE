<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingScan extends Model
{
    protected $fillable = ['booking_id', 'admin_id', 'scanned_at'];

    protected $casts = ['scanned_at' => 'datetime'];

    public function booking(){
        return $this->belongsTo(Booking::class);
    }
    public function admin(){
        return $this->belongsTo(Admin::class);
    }
}
