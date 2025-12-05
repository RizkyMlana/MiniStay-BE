<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="Review",
 *   type="object",
 *   required={"booking_id","user_id","room_id","rating"},
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="booking_id", type="integer"),
 *   @OA\Property(property="user_id", type="integer"),
 *   @OA\Property(property="room_id", type="integer"),
 *   @OA\Property(property="rating", type="integer"),
 *   @OA\Property(property="comment", type="string", nullable=true),
 *   @OA\Property(property="is_visible", type="boolean"),
 *   @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
 * )
 */

class Review extends Model
{
    protected $fillable = ['booking_id', 'user_id', 'room_id', 'rating', 'comment', 'is_visible'];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function booking(){
        return $this->belongsTo(Booking::class);
    }
    public function room(){
        return $this-> belongsTo(Room::class);
    }
}
