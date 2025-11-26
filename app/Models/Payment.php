<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['booking_id', 'payment_method', 'xendit_invoice_id', 'xendit_status', 'invoice_url', 'amount', 'raw_response'];

    protected $casts = ['raw_response'=> 'array', 'amount'=>'decimal:2'];

    public function booking(){
        return $this->belongsTo(Booking::class);
    }
}
