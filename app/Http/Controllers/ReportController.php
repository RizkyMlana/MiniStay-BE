<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function daily(Request $request){
        $date = $request->query('date', now()->toDateString());
        $total = Payment::whereDate('paid_at', $date)
            ->where('status', 'confirmed')
            ->sum('amount');

        return response()->json([
            'date' => $date,
            'total_income' => $total,
        ]);
    }

    public function weekly(Request $request){
        $start = Carbon::parse($request->query('start'))->startOfDay();
        $end = Carbon::parse($request->query('end'))->endOfDay();

        $total = Payment::whereBetween('paid_at', [$start, $end])
            ->where('status', 'confirmed')
            ->sum('amount');
        
        return response()->json([
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'total_income' => $total,
        ]);
    }

    public function monthly(Request $request){
        $month = Carbon::parse($request->query('month', now()));

        $total = Payment::whereYear('paid_at', $month->year)
            ->whereMonth('paid_at', $month->month)
            ->where('status', 'confirmed')
            ->sum('amount');
        
        return response()->json([
            'month' => $month->format('Y-m'),
            'total_income' => $total,
        ]);
    }

    public function topRooms(){
        $data = Booking::select('room_id', DB::raw('COUNT(*) as total_bookings'))
            ->where('status', 'paid')
            ->groupBy('room_id')
            ->with('room:id,name')
            ->orderByDesc('total_bookings')
            ->get();

        return response()->json($data);
    }
}
