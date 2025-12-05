<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;

class AdminController extends Controller
{    
/**
 *  @OA\Info(
 *      title="MiniStay API",
 *      version="1.0.0"
 *  )
 *  @OA\Get(
 *      path="/api/admin/bookings",
 *      summary="Get list of all bookings",
 *      tags={"Admin - Booking"},
 *      @OA\Response(
 *          response=200,
 *          description="List of all bookings",
 *          @OA\JsonContent(
 *              type="array",
 *              @OA\Items(ref="#/components/schemas/Booking")
 *          )
 *      )
 *  ) 
 * 
 * 
 * 
 */
    public function listBooking(){
        $booking = Booking::with('user', 'room')
            ->latest()
            ->get();
        return response()->json($booking);
    }
/**
 * @OA\Get(
 *     path="/api/admin/bookings/{id}",
 *     summary="Get detailed information of a booking",
 *     tags={"Admin - Booking"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Booking ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Booking detail",
 *         @OA\JsonContent(ref="#/components/schemas/Booking")
 *     )
 * )
 */
    public function showBooking($id){
        $data = Booking::with(['user', 'room', 'payment'])->findOrFail($id);
        return response()->json($data);
    }

/**
 * @OA\Put(
 *     path="/api/admin/bookings/{id}/status",
 *     summary="Update booking status",
 *     tags={"Admin - Booking"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Booking ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="New status for the booking",
 *         @OA\JsonContent(
 *             required={"status"},
 *             @OA\Property(property="status", type="string", example="confirmed")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Status successfully updated",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="data", ref="#/components/schemas/Booking")
 *         )
 *     )
 * )
 */


    public function updateBookingStatus($id, Request $request){
        $request->validate([
            'status' => 'required|in:pending,confirmed,checked_in,completed,cancelled'
        ]);

        $booking = Booking::findOrFail($id);
        $booking->status = $request->status;
        $booking->save();

        return response()->json(['message' => 'Status updated', 'data' => $booking]);
    }

/**
 * @OA\Put(
 *     path="/api/admin/bookings/{id}/cancel",
 *     summary="Cancel a booking",
 *     tags={"Admin - Booking"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Booking ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Booking cancelled successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Booking Cancelled")
 *         )
 *     )
 * )
 */
    public function cancelBooking($id){
        $booking = Booking::findOrFail($id);
        $booking->status = 'cancelled';
        $booking->save();

        return response()->json(['message' => 'Booking Cancelled']);
    }


/**
 * @OA\Put(
 *     path="/api/admin/payments/{id}/confirm",
 *     summary="Confirm payment for a booking",
 *     tags={"Admin - Payment"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Payment ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Payment has been confirmed",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Payment Confirmed")
 *         )
 *     )
 * )
 */

    public function confirmPayment($id){
        $payment = Payment::findOrFail($id);
        $payment->status = 'paid';
        $payment->paid_at = now();
        $payment->save();

        $booking = Booking::find($payment->booking_id);
        $booking->status = 'paid';
        $booking->save();

        return response()->json(['message' => 'Payment Confirmed']);
    }
}
