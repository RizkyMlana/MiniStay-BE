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
            ->where(function ($q) use ($user) {
                if ($user->role === 'user') {
                    $q->where('user_id', $user->id);
                }
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
            'booking_id' => $booking->id,
            'sender_type' => $user->role,
            'sender_id' => $user->id,
            'content' => $request->content,
        ]);

        return response()->json($message, 201);
    }

    public function markAsRead($id)
    {
        $message = Message::findOrFail($id);

        $message->update([
            'read_at' => now(),
        ]);

        return response()->json(['message' => 'Message marked as read']);
    }
}
