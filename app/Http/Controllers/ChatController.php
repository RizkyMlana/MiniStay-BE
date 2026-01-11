<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request){
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $user = $request->user();

        $booking = Booking::where('id', $request->booking_id)
            ->when($user->role === 'user', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->firstOrFail();

        $messages = Message::where('booking_id', $booking->id)
            ->orderBy('created_at')
            ->get();

        return response()->json($messages);
    }


    public function store(Request $request){
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'content' => 'required|string',
        ]);

        $user = $request->user();

        $booking = Booking::where('id', $request->booking_id)
            ->when($user->role === 'user', fn ($q) =>
                $q->where('user_id', $user->id)
            )
            ->firstOrFail();

        $message = Message::create([
            'sender_type' => $user->role,
            'sender_id' => $user->id,
            'room_id' => $booking->room_id, // aman: dari booking
            'booking_id' => $booking->id,
            'content' => $request->content,
        ]);

        return response()->json($message, 201);
    }


    public function markAsRead($id, Request $request){
        $user = $request->user();

        $message = Message::where('id', $id)
            ->whereHas('booking', function ($q) use ($user) {
                if ($user->role === 'user') {
                    $q->where('user_id', $user->id);
                }
            })
            ->firstOrFail();

        $message->update([
            'read_at' => now(),
        ]);

        return response()->json(['message' => 'Message marked as read']);
    }

}
