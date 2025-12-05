<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="RoomAvailability",
 *   type="object",
 *   required={"room_id","date","status"},
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="room_id", type="integer"),
 *   @OA\Property(property="date", type="string", format="date"),
 *   @OA\Property(property="status", type="string", enum={"available", "booked"}),
 *   @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
 * )
 */

class RoomAvailable extends Model
{

    protected $table = 'room_availabilities';
    protected $fillable = ['room_id', 'date', 'status'];

    protected $casts = ['date' => 'date'];
    public function room() {
        return $this->belongsTo(Room::class);
    }
}
