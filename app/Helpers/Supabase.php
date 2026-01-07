<?php

namespace App\Helpers;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class Supabase
{
    public static function upload(UploadedFile $file, string $path): string{
        $supabaseUrl = rtrim(config('services.supabase.url'), '/');
        $supabaseBucket = config('services.supabase.bucket');
        $supabaseKey = config('services.supabase.key');

        $endpoint = "{$supabaseUrl}/storage/v1/object/{$supabaseBucket}/{$path}";

        $response = Http::withHeaders([
            'apiKey' => $supabaseKey,
            'Authorization' => "Bearer {$supabaseKey}",
            'Content-Type' => $file->getMimeType(),
        ])->send(
            'PUT',
            $endpoint,
            ['body' => fopen($file->getRealPath(), 'r')]
        );

        if(!$response->successful()) {
            throw new \Exception('Supabase upload failed: ' . $response->body());
        }

        return "{$supabaseUrl}/storage/v1/object/public/{$supabaseBucket}/{$path}";
    }
}