<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\Room;
use App\Models\RoomAvailable;
use App\Models\Booking;
use App\Models\Review;

class UserController extends Controller
{


    public function indexRooms(){
        $rooms = Room::with('photos')->get();

        return response()->json([
            'status' => 'success',
            'data' => $rooms
        ]);
    }

    public function showRoom(Room $room){
        $room->load(['photos']);

        return response()->json([
            'status' => 'success',
            'date' => $room
        ]);
    }

    public function calendarAvailability(Room $room){
        $calendar = RoomAvailable::where('room_id' , $room->id)
            ->orderBy('date')
            ->get(['date','status']);
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'room' => $room->id,
                'calendar' => $calendar
            ]
        ]);
    }
    public function bookRoom(Request $request, Room $room){
        $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
        ]);

        $unavailable = RoomAvailable::where('room_id', $room->id)
            ->whereBetween('date', [$request->check_in, $request->check_out])
            ->where('status', 'booked')
            ->exists();
        if ($unavailable){
            return response()->json([
                'status' => 'failed',
                'message' => 'Room unavailable for selected date'
            ], 422);
        }
        $days = (strtotime($request->check_out) - strtotime($request->check_in)) / 86400;
        $totalPrice = $days * $room->price_per_day;
        $booking = Booking::create([
            'user_id' => auth()->guard()->id(),
            'room_id' => $room->id,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'total_price' => $totalPrice,
            'status' => 'waiting_payment',
            'booking_code' => strtoupper(Str::random(8))
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $booking,
        ]);
    }
    public function myBooking(){
        $booking = Booking::with('room')
            ->where('user_id', auth()->guard('user')->id())
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $booking
        ]);

    }
    public function submitReview(Request $request, Booking $booking){
        if($booking->user_id !== auth()->guard('user')->id()){
            return response()->json([
                'status' => 'failed',
                 'message' => 'Unauthorized'
            ], 403);
        }
        if($booking->status !== 'completed'){
            return response()->json([
                'status'=>'failed',
                'data' => 'Review Only allowed when booking completed'
            ]);
        }
        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string',
        ]);

        $review = Review::create([
            'booking_id' => $booking->id,
            'user_id' => auth()->guard('user')->id(),
            'room_id' => $booking->room_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $review
        ]);
    }
    public function myReviews(){
        $review = Review::with('room')
            ->where('user_id', auth()->guard('user')->id())
            ->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $review
        ]);
    }

}
