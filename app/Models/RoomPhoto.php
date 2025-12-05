<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="RoomPhoto",
 *   type="object",
 *   required={"room_id","url"},
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="room_id", type="integer"),
 *   @OA\Property(property="url", type="string"),
 *   @OA\Property(property="is_360", type="boolean"),
 *   @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
 * )
 */

class RoomPhoto extends Model
{

    protected $fillable = ['room_id', 'url', 'is_360'];

    public function room(){
        return $this->belongsTo(Room::class);
    }
}
