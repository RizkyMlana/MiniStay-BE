<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomBlock;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    private function isRoomAvailableToday(int $roomId): bool{
        return !RoomBlock::where('room_id', $roomId)
            ->where('start_date', '<=', today())
            ->where('end_date', '>=', today())
            ->exists();
    }

    private function getNextAvailableDate(int $roomId): ?string{
        $activeBlock = RoomBlock::where('room_id', $roomId)
            ->where('end_date', '>=', today())
            ->orderBy('end_date', 'asc')
            ->first();

        return $activeBlock
            ? $activeBlock->end_date->addDay()->toDateString()
            : today()->toDateString();
    }
    public function index()
    {
        $rooms = Room::query()
            ->leftJoin('bookings', 'bookings.room_id', '=', 'rooms.id')
            ->leftJoin('ratings', function ($join) {
                $join->on('ratings.booking_id', '=', 'bookings.id')
                    ->where('ratings.is_visible', true);
            })
            ->select(
                'rooms.id',
                'rooms.name',
                'rooms.price_per_day',
                'rooms.type',
                'rooms.facilities',
                'rooms.location',
                'rooms.description',
            )
            ->selectRaw('AVG(ratings.rating) as avg_rating')
            ->groupBy(
                'rooms.id',
                'rooms.name',
                'rooms.price_per_day',
                'rooms.facilities',
                'rooms.type',
                'rooms.location',
                'rooms.description'
            )
            ->with(['images' => function ($q) {
                $q->where('is_cover', true);
            }])
            ->get();

        $rooms->transform(function ($room) {
            return [
                'id' => $room->id,
                'name' => $room->name,
                'price_per_day' => $room->price_per_day,
                'type' => $room->type,
                'location' => $room->location,

                'rating' => $room->avg_rating
                    ? round($room->avg_rating, 1)
                    : null,

                'cover_image' => optional($room->images->first())->path,
                'facilities' => is_array($room->facilities) ? $room->facilities : json_decode($room->facilities, true),
                'description' => $room->description,
            ];
        });

        return response()->json($rooms);
    }

    public function show($id)
    {
        $room = Room::with('images')->findOrFail($id);

        $reviews = $room->bookings()
            ->whereHas('rating', fn($q) => $q->where('is_visible', true))
            ->with(['rating', 'user'])
            ->get()
            ->map(function($b) {
                return [
                    'id' => $b->rating->id,
                    'guestName' => $b->user->name ?? 'Tamu',
                    'rating' => $b->rating->rating,
                    'comment' => $b->rating->comment,
                    'date' => $b->created_at->format('Y-m-d'),
                ];
            });

        $avgRating = $reviews->avg('rating');
        $totalRatings = $reviews->count();

        return response()->json([
            'id' => $room->id,
            'name' => $room->name,
            'description' => $room->description,
            'price_per_day' => $room->price_per_day,
            'type' => $room->type,
            'facilities' => $room->facilities,
            'location' => $room->location,
            'images' => $room->images,
            'rating' => $avgRating ? round($avgRating, 1) : null,
            'total_ratings' => $totalRatings,
            'reviews' => $reviews,
        ]);
    }


}
