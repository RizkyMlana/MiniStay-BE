<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['booking_id', 'amount_requested', 'amount_paid', 'bank_name', 'bank_account', 'bank_owner', 'proof_url', 'status', 'expired_at'. 'paid_at'];

    protected $casts = [];

    public function booking(){
        return $this->belongsTo(Booking::class);
    }
}
