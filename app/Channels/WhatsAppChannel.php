<?php

namespace App\Channels;

use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    public function send(string $phone, string $message): void
    {
        Log::info('Send WA', [
            'to' => $phone,
            'message' => $message,
        ]);
    }
}