<?php

namespace App\Http\Controllers;

use App\Helpers\WhatsApp;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;


class AuthController extends Controller
{
    public function requestOtp(Request $request)
    {
        $request->validate([
            'name'  => 'required|string',
            'phone' => 'required|string',
        ]);

        $phone  = WhatsApp::normalizePhone($request->phone);
        $otpKey = "otp:{$phone}";

        if (Cache::has($otpKey)) {
            return response()->json([
                'message' => 'OTP masih aktif. Silakan cek WhatsApp Anda.'
            ], 429);
        }

        $otp = random_int(100000, 999999);

        Cache::put($otpKey, [
            'hash' => Hash::make($otp),
            'name' => $request->name,
        ], now()->addMinutes(5));

        WhatsApp::sendOtp($phone, (string) $otp);

        return response()->json([
            'message' => 'OTP sent'
        ]);
    }



    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp'   => 'required|numeric',
        ]);

        $phone  = WhatsApp::normalizePhone($request->phone);
        $otpKey = "otp:{$phone}";

        $data = Cache::get($otpKey);

        if (
            !$data ||
            !Hash::check($request->otp, $data['hash'])
        ) {
            return response()->json([
                'message' => 'Invalid or expired OTP'
            ], 422);
        }

        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => $data['name'],
                'role' => 'user',
            ]
        );

        Cache::forget($otpKey);

        $token = $user->createToken('user-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $user
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
