<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500'
        ]);

        $user = $request->user();
        $booking = Booking::where('id', $request->booking_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if($booking->status !== 'completed') {
            return response()->json([
                'message' => 'Booking belum selesai'
            ], 422);
        }
        if ($booking->rating) {
            return response()->json([
                'message' => 'Rating sudah dikirim'
            ], 409);
        }

        $rating = Rating::create([
            'booking_id' => $booking->id,
            'rating'     => $request->rating,
            'comment'    => $request->comment,
            'is_visible' => true,
        ]);

        return response()->json([
            'message' => 'Rating berhasil dikirim',
            'data' => $rating
        ]);

    }

    public function roomRatings($roomId){
        $rating = Rating::whereHas('booking', function ($q) use ($roomId) {
            $q->where('room_id', $roomId);
        })
        ->where('is_visible', true)
        ->with('booking.user:id,name')
        ->latest()
        ->get();

        return response()->json($rating);
    }
}
