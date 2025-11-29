<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\RoomAvailable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Http;

class BookingController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'room_id'=>'required|exists:rooms_id',
            'check_in'=>'required|date',
            'check_out'=> 'required|date|after:check_in',
        ]);

        $room = Room::find($request->room_id);

        $period = CarbonPeriod::create($request->check_in, $request->check_out)->toArray();
        $days = count($period);
        $total = $room->price_per_day * $days;

        $bookingCode = 'BK-' . now()->format('Ymd') . '-' . strtoupper(str::random(6));

        $booking = Booking::create([
            'user_id'=>auth()->id(),
            'room_id'=>$room->id,
            'check_in'=>$request->check_in,
            'check_out'=>$request->check_out,
            'total_price'=>$total,
            'booking_code'=>$bookingCode,
            'status'=>'pending',   
        ]);

        $invoice = Http::withBasicAuth(env('XENDIT_SECRET_KEY'), '')
            ->post('https://api.xendit.co/v2/invoices', [
                'external_id' => $bookingCode,
                'amount' => $total,
                'description'=>'Booking Room ' . $room->name,
                'costomer' => [
                    'email' => auth()->user()->email ?? 'noemail@example.com'
                ]
            ])->json();
        
        $booking->update([
            'xendit_invoice_id'=>$invoice['id']
        ]);

        return response()->json([
            'message'=> 'Booking created, waiting for payment',
            'invoice_url'=>$invoice['invoice_url'],
            'booking'=>$booking
        ]);
    }
    public function xenditCallback(Request $request){
        if($request->status == 'PAID'){
            $booking = Booking::where('xendit_invoice_id', $request->id)->first();

            if($booking){
                $dates = CarbonPeriod::create($booking->check_in, $booking->check_out)
                    ->map(fn($d) => $d->format('Y-m-d'))
                    ->toArray();
            
                RoomAvailable::where('room_id', $booking->room_id)
                    ->whereIn('date', $dates)
                    ->update(['is_available' => 0]);
            
                $booking->update([
                    'status'=> 'confirmed'
                ]);
            }
        }
        return response()->json(['status' => 'ok']);
    }
}
