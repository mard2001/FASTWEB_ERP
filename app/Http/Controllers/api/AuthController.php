<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|string|max:255',
            'password' => 'required|string|min:8|max:255'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['response' => 'Account invalid']);
        }

        $token = $user->createToken($user->token . 'Auth-Token')->plainTextToken;

        // Set and save the expiration date (1 day from now)

        return response()->json([
            'message' => 'Login Successful',
            'token_type' => 'Bearer',
            'token' => $token
        ])->cookie(
            'auth_token', 
            $token, 
            60 * 24,  // Cookie duration (1 day)
            null, 
            null, 
            true,  // Secure (true for HTTPS)
            true,  // HttpOnly (prevents JS access)
            false, // SameSite (not set by default)
            'None'  // Ensures the cookie works in cross-site contexts
        );
        
    }

    public function register(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|string|max:255',
            'password' => 'required|string|min:8|max:255|confirmed',
            'mobile' => 'required|string|min:11|max:11'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'password' => Hash::make($request->password)
        ]);

        if ($user) {

            $token = $user->createToken($user->token . 'Auth-Token')->plainTextToken;

            return response()->json([
                'message' => 'Registration Successful',
                'token_type' => 'Bearer',
                'token' => $token
            ]);
        } else {

            return response()->json([
                'message' => 'Error registration',
            ]);
        }
    }
}
