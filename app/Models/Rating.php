<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = [
        'booking_id',
        'rating',
        'comment',
        'is_visible',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
