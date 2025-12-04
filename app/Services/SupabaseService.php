<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SupabaseService {
    public static function upload($file, $filePath){
        $url = env('SUPABASE_URL') . "/storage/v1/object/" . env('SUPABASE_BUCKET') . "/$filePath";
        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_KEY'),
            'Content-Type' => $file->getClientMimeType()
        ])->put($url, file_get_contents($file));

        if(!$response->successful()){
            throw new \Exception('Supabase upload failed: ' . $response->body());
        }

        return env('SUPABASE_URL') . "/storage/v1/object/public/" . env('SUPABASE_BUCKET ') . "/$filePath";
    }
}