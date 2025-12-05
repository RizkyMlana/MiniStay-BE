<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * @OA\Schema(
 *   schema="Booking",
 *   type="object",
 *   required={"user_id","room_id","check_in","check_out","total_price","booking_code"},
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="user_id", type="integer"),
 *   @OA\Property(property="room_id", type="integer"),
 *   @OA\Property(property="check_in", type="string", format="date"),
 *   @OA\Property(property="check_out", type="string", format="date"),
 *   @OA\Property(property="total_price", type="integer"),
 *   @OA\Property(property="booking_code", type="string"),
 *   @OA\Property(property="status", type="string", enum={
 *       "pending","waiting_payment","paid","cancelled","checked_in","completed"
 *   }),
 *   @OA\Property(property="qr_code_url", type="string", nullable=true),
 *   @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
 * )
 */
class Booking extends Model
{

    protected $dates = ['user_id','room_id', 'check_in', 'check_out', 'total_price', 'booking_code', 'status', 'qr_code_url'];
    protected $casts = ['check_in'=>'date', 'check_out'=>'date'];
    
    public function user() {
        return $this->belongsTo(User::class);
    }
    public function room(){
        return $this->belongsTo(Room::class);
    }
    public function payments(){
        return $this->hasOne(Payment::class);
    }
    public function review(){
        return $this->hasOne(Review::class);
    }
    public function scan(){
        return $this->hasOne(BookingScan::class);
    }
}
