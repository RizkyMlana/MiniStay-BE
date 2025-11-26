<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomAvailable extends Model
{
    protected $fillable = ['room_id', 'date', 'status'];

    protected $casts = ['date' => 'date'];
    public function room() {
        return $this->belongsTo(Room::class);
    }
}
