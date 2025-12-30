<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\RoomBlock;
use Carbon\Carbon;

class AvailabilityService{
    public function isAvailable(int $roomId, string $checkIn, string $checkOut): bool{
        $checkIn = Carbon::parse($checkIn)->startOfDay();
        $checkOut = Carbon::parse($checkOut)->startOfDay();

        if ($checkOut <= $checkIn) {
            throw new \InvalidArgumentException('Check-out must be after check-in');
        }

        $bookingConflict = Booking::where('room_id', $roomId)
            ->whereIn('status', ['pending', 'paid'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->where('check_in_date', '<', $checkOut)
                ->where('check_out_date', '>', $checkIn);
            })
            ->exists();
        if($bookingConflict) {
            return false;
        }

        $blockConflict =  RoomBlock::where('room_id', $roomId)
            ->where(function ($q) use ($checkIn, $checkOut){
                $q->where('start_date', '<', $checkOut)
                ->where('end_date', '>', $checkIn);
            })
            ->exists();
        
        if($blockConflict){
            return false;
        }

        return true;
    }
}