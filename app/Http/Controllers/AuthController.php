<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\OtpCode;

class AuthController extends Controller
{

    public function loginAdmin(Request $request)
    {
        $request->validate([
            'name'=> 'required',
            'password'=> 'required',
        ]);

        if (Auth::guard('admin')->attempt($request->only('name', 'password'))) {
            $request->session()->regenerate();

            return response()->json([
                'message'=> 'Admin login successful',
                'role' => 'admin'
            ]);
        }

        return response()->json(['message' => 'name atau password salah'], 401);
    }

    public function generateOtp(Request $request)
    {
        $request->validate([
            'name' => 'required|max:25',
            'phone' => 'required'
        ]);

        $phone = $request->phone;
        $name = $request->name;

        $user = User::firstOrCreate(
            ['phone' => $phone],
            ['name' => $name]
        );

        $otpCode = rand(100000, 999999);

        OtpCode::where('phone', $phone)->delete();

        OtpCode::create([
            'phone' => $phone,
            'code' => $otpCode,
            'expires_at' => now()->addMinutes(5)
        ]);

        return response()->json([
            'message' => 'OTP generated',
            'otp_debug' => $otpCode
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|digits:6',
        ]);

        $otp = OtpCode::where('phone', $request->phone)
                    ->where('code', $request->code)
                    ->first();

        if (!$otp) {
            return response()->json(['message' => 'OTP salah'], 400);
        }

        if (now()->greaterThan($otp->expires_at)) {
            return response()->json(['message' => 'OTP kadaluarsa'], 400);
        }

        $user = User::where('phone', $request->phone)->first();

        $otp->delete();

        $token = $user->createToken('user_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $user
        ]);
    }


    public function logout(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
            return response()->json(['message' => 'Admin logout berhasil']);
        }

        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'User logout berhasil']);
        }

        return response()->json(['message' => 'Tidak ada sesi login'], 400);
    }
}
