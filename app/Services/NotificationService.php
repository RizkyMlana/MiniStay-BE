<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Channels\WhatsAppChannel;

class NotificationService
{
    protected WhatsAppChannel $wa;

    public function __construct(WhatsAppChannel $wa)
    {
        $this->wa = $wa;
    }

    public function bookingCreated(Booking $booking): void
    {
        $this->wa->send(
            $booking->user->phone,
            "Booking #{$booking->code} berhasil dibuat.\n" .
            "Total: Rp" . number_format($booking->total_price) . "\n" .
            "Silakan lakukan pembayaran."
        );
    }

    public function paymentInstruction(Payment $payment): void
    {
        $booking = $payment->booking;

        $this->wa->send(
            $booking->user->phone,
            "Instruksi Pembayaran\n" .
            "Booking: {$booking->code}\n" .
            "Nominal: Rp" . number_format($payment->amount) . "\n" .
            "Transfer ke: BCA 123456789 a.n MiniStay"
        );
    }

    public function paymentConfirmed(Payment $payment): void
    {
        $booking = $payment->booking;

        $this->wa->send(
            $booking->user->phone,
            "Pembayaran dikonfirmasi.\n" .
            "Booking #{$booking->code} sudah aktif.\n" .
            "Check-in: {$booking->check_in_date}"
        );
    }

    public function checkinReminder(Booking $booking): void
    {
        $this->wa->send(
            $booking->user->phone,
            "Reminder Check-in\n" .
            "Booking #{$booking->code}\n" .
            "Tanggal: {$booking->check_in_date}"
        );
    }

    public function checkoutReminder(Booking $booking): void
    {
        $this->wa->send(
            $booking->user->phone,
            "Reminder Check-out\n" .
            "Booking #{$booking->code}\n" .
            "Tanggal: {$booking->check_out_date}"
        );
    }
}