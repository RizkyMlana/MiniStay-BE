<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Room extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'price_per_day','description','facilities'
    ];

    protected $casts = ['facilities' => 'array'];
    public function photos(){
        return $this->hasMany(RoomPhoto::class);
    }
    public function availabilities(){
        return $this->hasMany(RoomAvailable::class);
    }
    public function bookings() {
        return $this->hasMany(Booking::class);
    }
    public function reviews() {
        return $this->hasMany(Review::class);
    }
    
}
