<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'mobile' => 'required|unique:users|string|min:10|max:10',
            'email' => 'required|email|string|max:255|unique:users,email',
        ]);
    
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $firstField = array_key_first($errors);
        
            return response()->json([
                'response_stat' => 0,
                'field' => $firstField,
                'message' => $errors[$firstField][0], // first error message for that field
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'user_type' => 'user',
                'password' => Hash::make('admin123')
            ]);

            if ($user) {
                $token = $user->createToken($user->name . 'Auth-Token')->plainTextToken;

                return response()->json([
                    'response_stat' => 1,
                    'message' => 'Registration Successful',
                    'token_type' => 'Bearer',
                    'token' => $token
                ]);
            }

            return response()->json([
                'response_stat' => 0,
                'message' => 'Registration failed. Please try again.',
            ], status: 500);

        } catch (\Exception $e) {
            return response()->json([
                'response_stat' => 0,
                'message' => 'An error occurred during registration.',
                'error' => $e->getMessage() // optional, for debugging
            ], 500);
        }
    }

}
