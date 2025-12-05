<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ReportController extends Controller
{
/**
 * @OA\Get(
 *     path="/api/admin/reports/daily",
 *     tags={"Admin - Reports"},
 *     summary="Get daily revenue report",
 *     security={{"adminAuth":{}}},
 * 
 *     @OA\Response(
 *         response=200,
 *         description="Daily revenue grouped by date",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 @OA\Property(property="date", type="string", example="2025-01-10"),
 *                 @OA\Property(property="total", type="integer", example=1500000)
 *             )
 *         )
 *     )
 * )
 */

    public function dailyReport(){
        $data = Booking::select(DB::raw("DATE(created_at) as date"), DB::raw("SUM(total_price) as total"))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($data);
    }
/**
 * @OA\Get(
 *     path="/api/admin/reports/weekly",
 *     tags={"Admin - Reports"},
 *     summary="Get weekly revenue report",
 *     security={{"adminAuth":{}}},
 * 
 *     @OA\Response(
 *         response=200,
 *         description="Weekly revenue grouped by ISO week",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 @OA\Property(property="week", type="string", example="202501"),
 *                 @OA\Property(property="total", type="integer", example=4200000)
 *             )
 *         )
 *     )
 * )
 */

    public function weeklyReport(){
        $data = Booking::select(DB::raw("YEARWEEK(created_at) as week"), DB::raw("SUM(total_price) as total"))
            ->groupBy('week')
            ->orderBy('week', 'desc')
            ->get();

        return response()->json($data);
    }
/**
 * @OA\Get(
 *     path="/api/admin/reports/monthly",
 *     tags={"Admin - Reports"},
 *     summary="Get monthly revenue report",
 *     security={{"adminAuth":{}}},
 * 
 *     @OA\Response(
 *         response=200,
 *         description="Monthly revenue grouped by year-month",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 @OA\Property(property="month", type="string", example="2025-01"),
 *                 @OA\Property(property="total", type="integer", example=12500000)
 *             )
 *         )
 *     )
 * )
 */

    public function monthlyReport(){
        $data = Booking::select(DB::raw("DATE_FORMAT(created_at, %Y-%m) as month"), DB::raw('SUM(total_price) as total'))
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        return response()->json($data);
    }

/**
 * @OA\Get(
 *     path="/api/admin/reports/room/popular",
 *     tags={"Admin - Reports"},
 *     summary="Get most booked rooms",
 *     security={{"adminAuth":{}}},
 * 
 *     @OA\Response(
 *         response=200,
 *         description="List of rooms ordered by most bookings",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 @OA\Property(property="room_id", type="integer", example=3),
 *                 @OA\Property(property="count", type="integer", example=42),
 *                 @OA\Property(
 *                     property="room",
 *                     ref="#/components/schemas/Room"
 *                 )
 *             )
 *         )
 *     )
 * )
 */

    public function mostBookedRoom(){
        $data = Booking::select('room_id', DB::raw("COUNT(*) as count"))
            ->groupBy('room_id')
            ->orderBy('count', 'desc')
            ->with('room')
            ->get();

        return response()->json($data);
    }


}
