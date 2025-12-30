<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CheckinService;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    public function checkin(Request $request, CheckinService $service)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        try {
            $booking = $service->checkin($request->token);

            return response()->json([
                'message' => 'Check-in success',
                'booking' => $booking->code,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
