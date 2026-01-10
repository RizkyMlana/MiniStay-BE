<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;

class AdminRatingController extends Controller
{
    public function index(){
        return Rating::with([
            'booking.user:id,name',
            'booking.room:id,name'
        ])
        ->latest()
        ->get();
    }

    public function toggleVisibility($id){
        $rating = Rating::findOrFail($id);

        $rating->update([
            'is_visible' => ! $rating->is_visible
        ]);
    }
}
