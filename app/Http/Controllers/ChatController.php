<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request){
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'booking_id' => 'nullable|exists:bookings,id',
        ]);

        $message = Message::where('room_id', $request->room_id)
            ->when($request->booking_id, function ($q) use ($request) {
                $q->where('booking_id', $request->booking_id);
            })
            ->orderBy('created_at')
            ->get();

        return response()->json($message);
    }

    public function store(Request $request){
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'content' => 'required|string',
        ]);
        $user = $request->user();
        $message = Message::create([
            'sender_type' => $user->role, 
            'sender_id' => $user->id,
            'room_id' => $request->room_id,
            'booking_id' => $request->booking_id,
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
