<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Admin;


class AuthController extends Controller
{
    public function registerAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|min:6'
        ]);

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::guard('admin')->login($admin);

        return response()->json([
            'message' => 'Admin registered successfully',
            'admin' => $admin,
            'role' => 'admin'
        ], 201);
    }

    public function login(Request $request)
    {
        if ($request->has('phone') && $request->has('otp')) {
            return $this->loginUserOtp($request);
        }

        if ($request->has('email') && $request->has('password')) {
            return $this->adminLogin($request);
        }

        return response()->json([
            'message' => 'Invalid login request'
        ], 400);
    }

    public function logout(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }

        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil'
        ]);
    }

    private function loginUserOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'otp' => 'required',
        ]);

        $otp = DB::table('otp_codes')
            ->where('phone', $request->phone)
            ->where('code', $request->otp)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return response()->json(['message' => 'OTP salah atau expired'], 401);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        DB::table('otp_codes')->where('phone', $request->phone)->delete();

        Auth::guard('web')->login($user);

        return response()->json([
            'message' => 'User login successful',
            'role' => 'user'
        ]);
    }

    private function adminLogin(Request $request)
    {
        $request->validate([
            'email'=> 'required|email',
            'password'=> 'required',
        ]);

        if (Auth::guard('admin')->attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();

            return response()->json([
                'message'=> 'Admin login successful',
                'role' => 'admin'
            ]);
        }

        return response()->json(['message' => 'Email atau password salah'], 401);
    }

    public function generateOtp(Request $request){
        $request->validate([
            'phone' => 'required',
        ]);
        $otpCode = rand(100000, 999999);

        DB::table('otp_codes')->updateOrInsert(
            ['phone' => $request->phone],
            [
                'code' => $otpCode,
                'expires_at' => now()->addMinutes(5)
            ]
        );
        return response()->json([
            'message'=> 'OTP generated',
            'phone' => $request->phone,
            'otp' => $otpCode,
            'expires_in' => 300
        ]);
        
    }
}