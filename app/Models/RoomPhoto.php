<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class RoomPhoto extends Model
{

    protected $fillable = ['room_id', 'url', 'is_360'];

    public function room(){
        return $this->belongsTo(Room::class);
    }
}
