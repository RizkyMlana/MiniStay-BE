<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomAvailable;
use App\Models\RoomPhoto;
use Carbon\CarbonPeriod;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AdminController extends Controller
{    
    public function listRoom(){
        $room = Room::with('availbilities')->paginate(10);
        return response()->json($room);
    }

    public function createRoom(Request $request){
        $request->validate([
            'name'=>'required',
            'price_per_day'=>'required|numeric',
            'description'=>'nullable|string',
            'facilities'=>'nullable|array',
            'photo'=>'nullable|image|max:2048',
        ]);

        $photoUrl = null;

        if($request->hasFile('photo')) {
            $photoUrl = $request->file('photo')->store('rooms', 'public');
        }

        $room = Room::create([
            'name'=>$request->name,
            'price_per_day'=>$request->price_per_day,
            'description'=>$request->description,
            'facilities'=> json_encode($request->facilities),
            'photo' => $photoUrl
        ]);

        return response()->json($room, 201);
    }

    public function updateRoom(Request $request, $roomId){
        $room = Room::findOrFail($roomId);

        if($request->hasFile('photo')) {
            if($room->photo) Storage::disk('public')->delete($room->photo);
            $room->photo = $request->file('photo')->store('rooms', 'public');
        }

        $room->update([
            'name'=>$request->name ?? $room->name,
            'price_per_day'=>$request->price_per_day ?? $room->price_per_day,
            'description'=>$request->description ?? $room->description,
            'facilities'=>$request->facilities ? json_encode($request->facilities) : $room->facilities
        ]);

        return response()->json($room);
    }


    public function setRoomStatus(Request $request, $roomId){
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status'=> 'required|in:occupied,available'
        ]);

        $dates = $this->generateDateRange($request->start_date, $request->end_date);
        foreach ($dates as $d){
            RoomAvailable::updateOrCreate(
                ['room_id'=>$roomId, 'date' => $d],
                ['status'=>$request->status]
            );

        }
        return response()->json(['message'=>'Room status updated']);
    }

    public function getRoomCalendar($roomId, Request $request){
        $year = $request->year ?? Carbon::now()->year;
        $month = $request->month ?? Carbon::now()->month;

        $start = Carbon::create($year, $month, 1);
        $end = $start->copy()->endOfMonth();

        $calendar = RoomAvailable::where('room_id', $roomId)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy('date');

        
        return response()->json($calendar);
    }

    public function getBookings(Request $request){
        $status = $request->status;
        $booking = Booking::with(['user', 'room'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->paginate(10);
        
        return response()->json($booking);
    }

    public function confirmPayment($bookingId){
        $booking = Booking::findOrFail($bookingId);
        $booking->status = 'paid';
        $booking->save();

        $this->sendNotification($booking->user_id, 'Pembayaran booking {$booking->booking_code} dikonfirmasi');

        return response()->json(['message'=>'Payment confirmed']);        
    }
    public function cancelBooking($bookingId){
        $booking = Booking::findOrFail($bookingId);
        $booking->status = 'cancelled';
        $booking->save();

        return response()->json(['message'=> 'Booking cancelled']);
    }



    public function getRevenueReport(Request $request){
        $type = $request->type ?? 'monthly';

        if($type == 'daily') {
            $data = Booking::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_price) as revenue'))
                ->where('status', 'paid')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        }
        else if ($type == 'weekly') {
            $data = Booking::select(DB::raw('YEARWEEK(created_at) as week'), DB::raw('SUM(total_price) as revenue'))
                ->where('status', 'paid')
                ->groupBy('week')
                ->orderBy('week')
                ->get();
        }
        else {
            $data = Booking::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m" as month'), DB::raw('SUM(total_price) as revenue'))
                ->where('status', 'paid')
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        }
        return response()->json($data);
    }
    public function uploadRoomPhoto(Request $request, $roomId){
        $request->validate([
            'photo' => 'required|image|max:5000'
        ]);

        $file = $request->file('photo');
        $fileName = 'room_ ' . $roomId . '_' . time() . '.' . $file->getClientOriginalExtension();

        $fileContent = file_get_contents($file->getRealPath());

        $supaUrl = env('SUPABASE_URL');
        $supaBucket = env('SUPABASE_BUCKET');
        $supaapiKey = env('SUPABASE_KEY');

        $uploadUrl = "$supaUrl/storage/v1/object/$supaBucket/$fileName";

        $response = Http::withHeaders([
            'apiKey' => $supaapiKey,
            'Authorization' => 'Bearer ' . $supaapiKey,
            'Content-Type' => $file->getMimeType(),
        ])->put($uploadUrl, $fileContent);

        if(!$response->successful()) {
            return response()->json([
                'error' => 'Failed to upload',
                'supabase_response' => $response->body()
            ], 500);
        }

        $publicUrl = "$supaUrl/storage/v1/object/public/$supaBucket/$fileName";

        RoomPhoto::create([
            'room_id' => $roomId,
            'url' => $publicUrl,
        ]);

        return response()->json([
            'message' => 'Photo uploaded successfully',
            'url' => $publicUrl
        ]);
    }
    private function sendNotification($userId, $message){
        return true;
    }



    private function generateDateRange($start, $end){
        $dates = [];
        $current = Carbon::parse($start);
        $end = Carbon::parse($end);

        while($current->lte($end)){
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }
        return $dates;
    }
}
