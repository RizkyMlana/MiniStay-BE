<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserAuthController extends Controller
{
    public function login(Request $request){
        $request->validate([
            'phone' => 'required|string',
            'name' => 'nullable|string',
            // 'otp' => 'required|string',
        ]);

        $user = User::firstOrCreate(
            ['phone' => $request->phone],
            [
                'name' => $request->name ?? 'Guest',
                'role' => 'user',
            ]
        );

        $user->tokens()->delete();
        $token = $user->createToken('user-token')->plainTextToken;

        return response()->json([
            'message' => 'Login Succesful',
            'token' => $token,
            'role' => 'user',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
            ]
        ]);
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout Succesfull'
        ]);
    }
}
