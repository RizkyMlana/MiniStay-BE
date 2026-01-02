<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public function index(){
        if(!auth()->user()->isAdmin()) {
            abort(403);
        }
        return Booking::latest()->paginate(20);
    }
}
