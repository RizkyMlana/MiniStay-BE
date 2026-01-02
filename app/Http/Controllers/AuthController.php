<?php

namespace App\Http\Controllers;

use App\Helpers\WhatsApp;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{
    public function loginAdmin(Request $request){
        $data = $request->validate([
            'name' => 'required',
            'password' => 'required',
        ]);

        $admin = User::where('name', $data['name'])
            ->where('role', 'admin')
            ->first();

        if(!$admin || !Hash::check($data['password'], $admin->password)) {
            throw ValidationException::withMessages([
                'name' => ['Invalid Credentials'],
            ]);
        }
        return response()->json([
            'token' => $admin->createToken('admin')->plainTextToken,
            'user' => $admin,
        ]);
    }
    public function requestOtp(Request $request){
        $request->validate([
            'phone' => 'required',
        ]);

        $user = User::firstOrCreate(
            ['phone' => $request->phone],
            ['role' => 'user']
        );

        $otp = rand(100000, 9999999);

        cache()->put('otp_'.$user->phone, $otp, now()->addMinutes(5));

        WhatsApp::send(
            $user->phone,
            'Kode OTP Ministay kamu: {$otp}'
        );

        return response()->json(['message' => 'OTP Sent']);
    }

    public function verifyOtp(Request $request){
        $data = $request->validate([
            'phone' => 'required',
            'otp' => 'required',

        ]);
        $cachedOtp = cache()->get('otp_'.$data['phone']);
        if($cachedOtp != $data['otp']) {
            abort(401, 'Invalid OTP');
        }

        $user = User::where('phone', $data['phone'])->firstOrFail();
        cache()->forget('otp_'.$data['phone']);

        return response()->json([
            'token' => $user->createToken('user')->plainTextToken,
            'user' => $user,
        ]);
    }
    public function logout(Request $request){
        $request->user()->currentAccesToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
