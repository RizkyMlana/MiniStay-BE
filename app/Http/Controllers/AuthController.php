<?php

namespace App\Http\Controllers;

use App\Helpers\WhatsApp;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{
    public function requestOtp(Request $request) {
        $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
        ]);

        $otp = rand(100000, 999999);

        Cache::put('otp_' . $request->phone, $otp, now()->addMinutes(5));

        WhatsApp::send(
            $request->phone,
            "Kode OTP kamu: {$otp}"
        );

        return response()->json([
            'message' => 'OTP Sent'
        ]);

    }

    public function verifyOtp(Request $request){
        $request->validate([
            'name' => 'required',
            'phone' => 'required|string',
            'otp' => 'required|numeric',
        ]);

        $cachedOtp = Cache::get('otp_' . $request->phone);

        if(!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json([
                'message' => 'Invalid or expired OTP'
            ], 422);
        }

        $user = User::firstOrCreate(
            ['phone' => $request->phone],
            [
                'name' => $request->name,
                'role' => 'user',
            ]
        );

        Cache::forget('otp_' . $request->phone);

        $token = $user->createToken('user-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }
    public function adminLogin(Request $request){
        $request->validate([
            'name' => 'required|string',
            'password' => 'required|string',
        ]);
        $admin = User::where('name', $request->name)
            ->where('role', 'admin')
            ->first();
        
        if(!$admin || ! Hash::check($request->password, $admin->password)) {
            return response()->json([
                'message' => 'Invalid admin credentials'
            ], 401);
        }

        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
            ],
        ]);
    }
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged Out'
        ]);
    }
}
