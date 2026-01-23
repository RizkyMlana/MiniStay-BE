<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function weeklyChart(Request $request)
    {
        $start = Carbon::parse($request->query('start'))->startOfDay();
        $end   = Carbon::parse($request->query('end'))->endOfDay();

        $data = Booking::query()
            ->selectRaw('DATE(updated_at) as date, SUM(total_price) as income')
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'label'  => Carbon::parse($item->date)->translatedFormat('l'),
                    'income' => (int) $item->income,
                ];
            });

        return response()->json($data);
    }

    public function monthlyChart(Request $request)
    {
        $month = Carbon::parse($request->query('month'));

        $data = Booking::query()
            ->selectRaw('WEEK(updated_at, 1) as week, SUM(total_price) as income')
            ->where('status', 'completed')
            ->whereYear('updated_at', $month->year)
            ->whereMonth('updated_at', $month->month)
            ->groupBy('week')
            ->orderBy('week')
            ->get()
            ->map(function ($item, $index) {
                return [
                    'label'  => 'Minggu ' . ($index + 1),
                    'income' => (int) $item->income,
                ];
            });

        return response()->json($data);
    }
    public function yearlyChart(Request $request)
    {
        $year = $request->query('year', now()->year);

        $data = Booking::query()
            ->selectRaw('MONTH(updated_at) as month, SUM(total_price) as income')
            ->where('status', 'completed')
            ->whereYear('updated_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'label'  => Carbon::create()->month($item->month)->translatedFormat('M'),
                    'income' => (int) $item->income,
                ];
            });


        return response()->json($data);
    }

    public function topRooms()
    {
        $topRooms = Booking::query()
            ->select('room_id', DB::raw('COUNT(*) as total_bookings'))
            ->where('status', 'completed')
            ->groupBy('room_id')
            ->with('room:id,name')
            ->orderByDesc('total_bookings')
            ->get();

        return response()->json($topRooms);
    }
}
