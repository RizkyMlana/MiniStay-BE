<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class WhatsApp
{
    public static function send(string $phone, string $message): void
    {
        Log::info('Send WhatsApp', [
            'to' => $phone,
            'message' => $message,
        ]);
    }
}
