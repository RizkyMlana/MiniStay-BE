<?php
namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomBlock;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService{
    protected AvailabilityService $availabilityService;
    
    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    public function create(
        int $userId,
        int $roomId,
        string $checkIn,
        string $checkOut,
    ): Booking {
        $checkInDate = Carbon::parse($checkIn)->startOfDay();
        $checkOutDate = Carbon::parse($checkOut)->startOfDay();

        if ($checkOutDate <= $checkInDate) {
            throw new \InvalidArgumentException('Invalid date range');
        }

        if (!$this->availabilityService->isAvailable(
            $roomId,
            $checkInDate,
            $checkOutDate
        )) {
            throw new \Exception('Room is not available');
        }

        $room = Room::findOrFail($roomId);
        $totalDays = $checkInDate->diffInDays($checkOutDate);
        $totalPrice = $totalDays * $room->price_per_day;

        return DB::transaction(function () use (
            $userId,
            $room,
            $checkInDate,
            $checkOutDate,
            $totalPrice,
        ) {
            $booking = Booking::create([
                'booking_code'   => $this->generateBookingCode(),
                'user_id'        => $userId,
                'room_id'        => $room->id,
                'check_in_date'  => $checkInDate,
                'check_out_date' => $checkOutDate,
                'status'         => 'pending',
                'total_price'    => $totalPrice,
            ]);
            RoomBlock::create([
                'room_id'    => $room->id,
                'booking_id' => $booking->id,
                'start_date' => $checkInDate,
                'end_date'   => $checkOutDate,
                'type'       => 'booking',
            ]);

            return $booking;
        });
    }
    protected function generateBookingCode(): string
    {
        return 'MS-' . strtoupper(uniqid());
    }
}