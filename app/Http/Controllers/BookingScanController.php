<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingScan;
use Illuminate\Http\Request;

class BookingScanController extends Controller
{
/**
 * @OA\Post(
 *     path="/api/admin/scan",
 *     tags={"Admin - Booking Scan"},
 *     summary="Scan QR booking untuk check-in",
 *     description="Memvalidasi booking_code dari QR dan mencatat data scan oleh admin.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"booking_code"},
 *             @OA\Property(property="booking_code", type="string", example="MNST123456")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="QR valid, check-in success",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="QR Valid,  check in success")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Booking code tidak ditemukan",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Booking not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Input tidak valid",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="The booking_code field is required.")
 *         )
 *     )
 * )
 */

    public function scanQr(Request $request){
        $request->validate(['booking_code' => 'required']);

        $booking = Booking::where('booking_code', $request->booking_code)->firstOrFail();

        BookingScan::create([
            'booking_id' => $booking->id,
            'admin_id' => auth()->guard('admin')->id(),
        ]);

        $booking->status = 'checked_in';
        $booking->save();

        return response()->json(['message' => 'QR Valid,  check in success']);
    }

/**
 * @OA\Get(
 *     path="/api/admin/scans",
 *     tags={"Admin - Booking Scan"},
 *     summary="List semua data scan QR",
 *     description="Menampilkan semua riwayat scan QR oleh admin, termasuk relasi booking dan admin.",
 *     @OA\Response(
 *         response=200,
 *         description="List of scans",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/BookingScan")
 *         )
 *     )
 * )
 */


    public function listScans(){
        $data = BookingScan::with(['booking', 'admin'])->latest()->get();
        return response()->json($data);
    }
}
