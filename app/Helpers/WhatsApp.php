<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WhatsApp
{
    const OTP_LIMIT  = 3;
    const OTP_WINDOW = 300; 


    public static function sendOtp(
        string $phone,
        string $otp,
        string $context = 'login'
    ): ?object {
        $phone = self::normalizePhone($phone);

        if (!self::checkOtpRateLimit($phone)) {
            return null;
        }

        return self::send([
            [
                'phone'   => $phone,
                'message' => "Kode OTP {$context} kamu adalah: {$otp}",
            ]
        ]);
    }

    public static function sendNotification(
        string $phone,
        string $title,
        string $message
    ): ?object {
        return self::send([
            [
                'phone'   => self::normalizePhone($phone),
                'message' => "{$title}\n{$message}",
            ]
        ]);
    }

    public static function sendBulk(array $phones, string $message): ?object
    {
        $data = [];

        foreach ($phones as $phone) {
            $data[] = [
                'phone'   => self::normalizePhone($phone),
                'message' => $message,
            ];
        }

        return self::send($data);
    }


    protected static function send(array $messages): ?object
    {
        $payload = [
            'device_key' => env('QUODS_DEVICE_KEY'),
            'data'       => $messages,
        ];

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . env('QUODS_API_TOKEN'),
                    'Content-Type'  => 'application/json',
                ])
                ->post(env('QUODS_API_URL'), $payload);

            if (!$response->successful()) {
                Log::error('WhatsApp API failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            return $response->object();

        } catch (\Throwable $e) {
            Log::error('WhatsApp API exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }


    protected static function checkOtpRateLimit(string $phone): bool
    {
        $key = "otp_limit:{$phone}";

        if (!Cache::has($key)) {
            Cache::put($key, 1, self::OTP_WINDOW);
            return true;
        }

        if (Cache::get($key) >= self::OTP_LIMIT) {
            Log::warning('OTP rate limit hit', [
                'phone' => self::maskPhone($phone),
            ]);
            return false;
        }

        Cache::increment($key);
        return true;
    }


    public static function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '0')) {
            return '62' . substr($phone, 1);
        }

        if (!str_starts_with($phone, '62')) {
            return '62' . $phone;
        }

        return $phone;
    }

    protected static function maskPhone(string $phone): string
    {
        return substr($phone, 0, 4) . '****' . substr($phone, -2);
    }

    public static function sendBookingConfirmation(
        string $phone,
        string $bookingCode,
        string $roomName,
        float $totalPrice,
        \Carbon\Carbon $paymentDeadline
    ): ?object {
        $title = "Konfirmasi Booking";
        $message = "Booking kamu ($bookingCode) untuk kamar $roomName berhasil. \n".
                    "Total pembayaran: Rp " . number_format($totalPrice, 0, ',', '.') . "\n".
                    "Silakan lakukan pembayaran sebelum " . $paymentDeadline->format('d-m-Y H:i') . "\n".
                    "Setelah transfer, booking akan dikonfirmasi.";

        return self::sendNotification($phone, $title, $message);
    }
}