<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
/**
 * @OA\Get(
 *     path="/api/reviews/{room_id}",
 *     tags={"User - Reviews"},
 *     summary="Get visible reviews for a room",
 *     description="Return only reviews where is_visible = true",
 * 
 *     @OA\Parameter(
 *         name="room_id",
 *         in="path",
 *         required=true,
 *         description="ID of the room",
 *         @OA\Schema(type="integer")
 *     ),
 * 
 *     @OA\Response(
 *         response=200,
 *         description="List of reviews",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Review")
 *         )
 *     )
 * )
 */

    public function listReview($id){
        return Review::where('room_id', $id)
            ->where('is_visible', true)
            ->get();
    }
/**
 * @OA\Patch(
 *     path="/api/admin/reviews/{id}/moderate",
 *     tags={"Admin - Reviews"},
 *     summary="Update review visibility",
 *     description="Admin can hide or show user reviews",
 *     security={{"adminAuth":{}}},
 * 
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Review ID",
 *         @OA\Schema(type="integer")
 *     ),
 * 
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="is_visible", type="boolean", example=true)
 *         )
 *     ),
 * 
 *     @OA\Response(
 *         response=200,
 *         description="Visibility updated",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Review visibility updated")
 *         )
 *     )
 * )
 */

    public function moderateReview(Request $request, $id){
        $review = Review::findOrFail($id);
        $review->is_visible = $request->is_visible;
        $review->save();

        return response()->json(['message' => 'Review visibility updated']);
    }
}
