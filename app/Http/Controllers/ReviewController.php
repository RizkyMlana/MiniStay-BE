<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function listReview($id){
        return Review::where('room_id', $id)
            ->where('is_visible', true)
            ->get();
    }

    public function moderateReview(Request $request, $id){
        $review = Review::findOrFail($id);
        $review->is_visible = $request->is_visible;
        $review->save();

        return response()->json(['message' => 'Review visibility updated']);
    }
}
