<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 * @OA\Schema(
 *   schema="Room",
 *   type="object",
 *   required={"name","price_per_day"},
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="price_per_day", type="integer"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="facilities", type="object", nullable=true),
 *   @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
 * )
 */

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
    public function firstPhoto(){
        return $this->hasOne(RoomPhoto::class)->orderBy('id', 'asc');
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
