<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function daily(Request $request)
    {
        $date = $request->query('date', now()->toDateString());

        $totalIncome = Payment::query()
            ->whereDate('confirmed_at', $date)
            ->where('status', 'confirmed')
            ->sum('amount');

        return response()->json([
            'date' => $date,
            'total_income' => $totalIncome,
        ]);
    }

    public function weekly(Request $request)
    {
        $start = Carbon::parse($request->query('start'))->startOfDay();
        $end   = Carbon::parse($request->query('end'))->endOfDay();

        $totalIncome = Payment::query()
            ->whereBetween('confirmed_at', [$start, $end])
            ->where('status', 'confirmed')
            ->sum('amount');

        return response()->json([
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'total_income' => $totalIncome,
        ]);
    }
    
    public function monthly(Request $request)
    {
        $month = Carbon::parse($request->query('month', now()));

        $totalIncome = Payment::query()
            ->whereYear('confirmed_at', $month->year)
            ->whereMonth('confirmed_at', $month->month)
            ->where('status', 'confirmed')
            ->sum('amount');

        return response()->json([
            'month' => $month->format('Y-m'),
            'total_income' => $totalIncome,
        ]);
    }
    public function weeklyChart(Request $request)
    {
        $start = Carbon::parse($request->query('start'))->startOfDay();
        $end   = Carbon::parse($request->query('end'))->endOfDay();

        $data = Payment::query()
            ->selectRaw('DATE(confirmed_at) as date, SUM(amount) as income')
            ->where('status', 'confirmed')
            ->whereBetween('confirmed_at', [$start, $end])
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

        $data = Payment::query()
            ->selectRaw('WEEK(confirmed_at, 1) as week, SUM(amount) as income')
            ->where('status', 'confirmed')
            ->whereYear('confirmed_at', $month->year)
            ->whereMonth('confirmed_at', $month->month)
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

        $data = Payment::query()
            ->selectRaw('MONTH(confirmed_at) as month, SUM(amount) as income')
            ->where('status', 'confirmed')
            ->whereYear('confirmed_at', $year)
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
            ->where('status', 'paid')
            ->groupBy('room_id')
            ->with('room:id,name')
            ->orderByDesc('total_bookings')
            ->get();

        return response()->json($topRooms);
    }
}
