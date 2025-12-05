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
/**
 * @OA\Get(
 *     path="user/rooms",
 *     summary="Get all rooms",
 *     tags={"User - Rooms"},
 *
 *     @OA\Response(
 *         response=200,
 *         description="List of all rooms",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/Room")
 *             )
 *         )
 *     )
 * )
 */


    public function indexRooms(){
        $rooms = Room::with('photos')->get();

        return response()->json([
            'status' => 'success',
            'data' => $rooms
        ]);
    }

/**
 * @OA\Get(
 *     path="/rooms/{room}",
 *     summary="Get room detail",
 *     tags={"User - Rooms"},
 *
 *     @OA\Parameter(
 *         name="room",
 *         in="path",
 *         required=true,
 *         description="Room ID",
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Room detail",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string"),
 *             @OA\Property(property="date", ref="#/components/schemas/Room")
 *         )
 *     )
 * )
 */

    public function showRoom(Room $room){
        $room->load(['photos']);

        return response()->json([
            'status' => 'success',
            'date' => $room
        ]);
    }

/**
 * @OA\Get(
 *     path="/rooms/{room}/calendar",
 *     summary="Get availability calendar for a room",
 *     tags={"User - Rooms"},
 *
 *     @OA\Parameter(
 *         name="room",
 *         in="path",
 *         required=true,
 *         description="Room ID",
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Room availability calendar",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="room", type="integer"),
 *                 @OA\Property(
 *                     property="calendar",
 *                     type="array",
 *                     @OA\Items(ref="#/components/schemas/RoomAvailability")
 *                 )
 *             )
 *         )
 *     )
 * )
 */

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
/**
 * @OA\Post(
 *     path="/rooms/{room}/book",
 *     summary="Create booking for a room",
 *     tags={"User - Booking"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="room",
 *         in="path",
 *         required=true,
 *         description="Room ID",
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"check_in","check_out"},
 *             @OA\Property(property="check_in", type="string", format="date"),
 *             @OA\Property(property="check_out", type="string", format="date")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Booking created",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string"),
 *             @OA\Property(property="data", ref="#/components/schemas/Booking")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Room unavailable or validation error"
 *     )
 * )
 */

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

/**
 * @OA\Get(
 *     path="/my-bookings",
 *     summary="Get user's bookings",
 *     tags={"User - Booking"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="List of user's bookings",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/Booking")
 *             )
 *         )
 *     )
 * )
 */

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

/**
 * @OA\Post(
 *     path="/bookings/{booking}/review",
 *     summary="Submit review for a completed booking",
 *     tags={"User - Reviews"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="booking",
 *         in="path",
 *         required=true,
 *         description="Booking ID",
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"rating"},
 *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5),
 *             @OA\Property(property="comment", type="string", nullable=true)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Review created",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string"),
 *             @OA\Property(property="data", ref="#/components/schemas/Review")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=403,
 *         description="Unauthorized"
 *     )
 * )
 */

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

/**
 * @OA\Get(
 *     path="/my-reviews",
 *     summary="Get reviews created by authenticated user",
 *     tags={"User - Reviews"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="List of user's reviews",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/Review")
 *             )
 *         )
 *     )
 * )
 */

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
