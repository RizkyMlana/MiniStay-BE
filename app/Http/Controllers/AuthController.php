<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\OtpCode;

class AuthController extends Controller
{

/**
 * @OA\Post(
 *     path="/api/auth/admin/login",
 *     tags={"Auth"},
 *     summary="Login sebagai admin",
 *     description="Autentikasi admin menggunakan username dan password.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name","password"},
 *             @OA\Property(property="name", type="string", example="akurajakauhitam"),
 *             @OA\Property(property="password", type="string", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Login berhasil",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Admin login successful"),
 *             @OA\Property(property="role", type="string", example="admin")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="name atau password salah",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="name atau password salah")
 *         )
 *     )
 * )
 */

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

/**
 * @OA\Post(
 *     path="/api/auth/logout",
 *     tags={"Auth"},
 *     summary="Logout admin atau user",
 *     description="Menghapus sesi autentikasi untuk admin (session) atau user (API token).",
 *     @OA\Response(
 *         response=200,
 *         description="Logout berhasil",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Admin logout berhasil")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Tidak ada sesi login",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Tidak ada sesi login")
 *         )
 *     )
 * )
 */

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
