<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="Payment",
 *   type="object",
 *   required={"booking_id","amount_requested"},
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="booking_id", type="integer"),
 *   @OA\Property(property="amount_requested", type="integer"),
 *   @OA\Property(property="amount_paid", type="integer", nullable=true),
 *   @OA\Property(property="bank_name", type="string", nullable=true),
 *   @OA\Property(property="bank_account", type="string", nullable=true),
 *   @OA\Property(property="bank_owner", type="string", nullable=true),
 *   @OA\Property(property="proof_url", type="string", nullable=true),
 *   @OA\Property(property="status", type="string", enum={"pending","waiting_confirmation","paid","failed"}),
 *   @OA\Property(property="expired_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="paid_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
 * )
 */

class Payment extends Model
{
    protected $fillable = ['booking_id', 'amount_requested', 'amount_paid', 'bank_name', 'bank_account', 'bank_owner', 'proof_url', 'status', 'expired_at'. 'paid_at'];

    protected $casts = [];

    public function booking(){
        return $this->belongsTo(Booking::class);
    }
}
