<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomAvailable;
use App\Models\RoomPhoto;
use Illuminate\Http\Request;
use App\Services\SupabaseService;

class RoomController extends Controller
{

/**
 * @OA\Post(
 *     path="/api/admin/rooms",
 *     tags={"Admin - Rooms"},
 *     summary="Create a new room",
 *     security={{"adminAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name","price_per_day"},
 *             @OA\Property(property="name", type="string", example="VIP Suite"),
 *             @OA\Property(property="price_per_day", type="integer", example=350000),
 *             @OA\Property(property="description", type="string", example="Kamar luas dengan fasilitas lengkap"),
 *             @OA\Property(
 *                 property="facilities",
 *                 type="array",
 *                 @OA\Items(type="string", example="AC, TV, WiFi")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Room created",
 *         @OA\JsonContent(ref="#/components/schemas/Room")
 *     )
 * )
 */

    public function createRoom(Request $request){
        $data = $request->validate([
            'name' => 'required',
            'price_per_day' => 'required|integer',
            'description' => 'nullable',
            'facilities' => 'nullable|array'
        ]);

        $room = Room::create($data);
        return response()->json($room);
    }

/**
 * @OA\Put(
 *     path="/api/admin/rooms/{id}",
 *     tags={"Admin - Rooms"},
 *     summary="Update room data",
 *     security={{"adminAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Room ID",
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="Superior Room"),
 *             @OA\Property(property="price_per_day", type="integer", example=250000),
 *             @OA\Property(property="description", type="string", example="Kamar nyaman untuk 2 orang"),
 *             @OA\Property(
 *                 property="facilities",
 *                 type="array",
 *                 @OA\Items(type="string")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Room updated",
 *         @OA\JsonContent(ref="#/components/schemas/Room")
 *     )
 * )
 */

    public function updateRoom(Request $request, $id){
        $room = Room::findOrFail($id);
        $room->update($request->all());
        return response()->json($room);
    }

/**
 * @OA\Delete(
 *     path="/api/admin/rooms/{id}",
 *     tags={"Admin - Rooms"},
 *     summary="Delete a room",
 *     security={{"adminAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Room ID",
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Room deleted",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Room deleted")
 *         )
 *     )
 * )
 */

    public function deleteRoom($id){
        Room::findOrFail($id)->delete();
        return response()->json(['message' => 'Room deleted']);
    }

/**
 * @OA\Post(
 *     path="/api/admin/rooms/{id}/upload-photo",
 *     tags={"Admin - Rooms"},
 *     summary="Upload room photo",
 *     security={{"adminAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Room ID",
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"photo"},
 *                 @OA\Property(
 *                     property="photo",
 *                     type="string",
 *                     format="binary"
 *                 ),
 *                 @OA\Property(
 *                     property="is_360",
 *                     type="boolean",
 *                     example=false
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Photo uploaded",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Photo uploaded"),
 *             @OA\Property(property="url", type="string", example="https://supabase.storage/rooms/photo123.jpg")
 *         )
 *     )
 * )
 */

    public function uploadPhoto(Request $request, $id){
        $request->validate([
            'photo' => 'required|file|image',
        ]);

        $file = $request->file('photo');
        $filename = "rooms/$id" . uniqid() . "." . $file->getClientOriginalExtension();

        $url = SupabaseService::upload($file, $filename);

        RoomPhoto::create([
            'room_id' => $id,
            'url' => $url,
            'is_360' => $request->is_360 ?? false
        ]);

        return response()->json(['message' => 'Photo uploaded', 'url' => $url]);
    }

/**
 * @OA\Post(
 *     path="/api/admin/rooms/{id}/availability",
 *     tags={"Admin - Rooms"},
 *     summary="Update room availability for a specific date",
 *     security={{"adminAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Room ID",
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"date","status"},
 *             @OA\Property(property="date", type="string", example="2025-01-10"),
 *             @OA\Property(property="status", type="string", enum={"available","booked"}, example="booked")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Availability updated",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Availability updated")
 *         )
 *     )
 * )
 */

    public function updateAvailability(Request $request, $id){
        $request->validate([
            'date' => 'required|date',
            'status' => 'required|in:available, booked'
        ]);

        RoomAvailable::updateOrCreate(
            ['room_id' => $id, 'date' => $request->date],
            ['status' => $request->status]
        );

        return response()->json(['message' => 'Availability updated']);
    }

/**
 * @OA\Get(
 *     path="/api/admin/rooms/{id}/calendar",
 *     tags={"Admin - Rooms"},
 *     summary="Get full availability calendar for a room",
 *     security={{"adminAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Room ID",
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Availability calendar",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/RoomAvailability")
 *         )
 *     )
 * )
 */

    public function calendarAdmin($id){
        $calendar = RoomAvailable::where('room_id', $id)
            ->orderBy('date')
            ->get();
        
            return response()->json($calendar);
    }
    
}
