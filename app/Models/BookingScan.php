<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="BookingScan",
 *   type="object",
 *   required={"booking_id"},
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="booking_id", type="integer"),
 *   @OA\Property(property="admin_id", type="integer", nullable=true),
 *   @OA\Property(property="scanned_at", type="string", format="date-time"),
 *   @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
 * )
 */

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
