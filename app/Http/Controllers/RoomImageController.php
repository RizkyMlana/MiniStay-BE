<?php

namespace App\Http\Controllers;

use App\Helpers\Supabase;
use App\Models\RoomImage;
use Illuminate\Http\Request;

class RoomImageController extends Controller
{
    public function store(Request $request, $roomId){
        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        $path = 'rooms/' . uniqid() . '.' . $request->image->extension();

        $url = Supabase::upload($request->image, $path);
        
        $image = RoomImage::create([
            'room_id' => $roomId,
            'path' => $url,
            'is_cover' => false,
        ]);

        return response()->json($image, 201);
    }
    public function update(Request $request, $id) {
        $image = RoomImage::findOrFail($id);

        $request->validate([
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = 'rooms/' . uniqid() . '.' . $request->image->extension();
            $url = Supabase::upload($request->image, $path);
            $image->update(['path' => $url]);
        }

        return response()->json($image);
    }


    public function setCover($id){
        $image = RoomImage::findOrFail($id);

        RoomImage::where('room_id', $image->room_id)
            ->update(['is_cover' => false]);

        $image->update(['is_cover' => true]);

        return response()->json(['message' => 'Cover updated']);
    }

    public function destroy($id){
        RoomImage::findOrFail($id)->delete();
        return response()->json(['message' => 'Image deleted']);
    }

    public function index($roomId)
    {
        $images = RoomImage::where('room_id', $roomId)
            ->orderByDesc('is_cover')
            ->orderBy('created_at')
            ->get();

        return response()->json($images);
    }

    
}
