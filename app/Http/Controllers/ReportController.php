<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ReportController extends Controller
{

    public function dailyReport(){
        $data = Booking::select(DB::raw("DATE(created_at) as date"), DB::raw("SUM(total_price) as total"))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($data);
    }


    public function weeklyReport(){
        $data = Booking::select(DB::raw("YEARWEEK(created_at) as week"), DB::raw("SUM(total_price) as total"))
            ->groupBy('week')
            ->orderBy('week', 'desc')
            ->get();

        return response()->json($data);
    }

    public function monthlyReport(){
        $data = Booking::select(DB::raw("DATE_FORMAT(created_at, %Y-%m) as month"), DB::raw('SUM(total_price) as total'))
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        return response()->json($data);
    }


    public function mostBookedRoom(){
        $data = Booking::select('room_id', DB::raw("COUNT(*) as count"))
            ->groupBy('room_id')
            ->orderBy('count', 'desc')
            ->with('room')
            ->get();

        return response()->json($data);
    }


}
