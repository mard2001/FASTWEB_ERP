<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

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

        // Fire the Login event for event listeners
        event(new Login('api', $user, false));

        // Log the login activity
        activity('authentication')
            ->causedBy($user)
            ->event('login')
            ->withProperties([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_method' => 'email'
            ])
            ->log('User logged into the system');

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

    public function logout(Request $request)
    {
        $user = $request->user();
        
        if ($user) {
            // Fire the Logout event for event listeners
            event(new Logout('api', $user));

            // Log the logout activity BEFORE deleting tokens
            try {
                activity('authentication')
                    ->causedBy($user)
                    ->event('logout')
                    ->withProperties([
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'logout_method' => 'api',
                        'user_id' => $user->id,
                        'user_name' => $user->name
                    ])
                    ->log('User logged out of the system');
            } catch (\Exception $e) {
                // Log error but don't fail the logout process
                Log::error('Failed to log logout activity: ' . $e->getMessage());
            }

            // Delete all tokens for this user
            $user->tokens()->delete();

            return response()->json([
                'message' => 'Logout Successful'
            ])->cookie(
                'auth_token', 
                null, 
                -1  // Expire the cookie immediately
            );
        }

        Log::warning('No user found for logout - returning 401');
        return response()->json(['message' => 'Already logged out'], 401);
    }

    public function forceLogout(Request $request)
    {
        // This method handles logout without requiring valid authentication
        // It's useful when the token might be expired or invalid
        
        $authHeader = $request->header('Authorization');
        $token = null;
        
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
        }
        
        if ($token) {
            try {
                // Try to find and delete the token
                $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
                if ($personalAccessToken) {
                    $user = $personalAccessToken->tokenable;
                    
                    if ($user) {
                        // Check if a logout activity was already logged recently (within last 10 seconds) to prevent duplicates
                        // Also check by IP and user agent to be more specific
                        $recentLogout = \Spatie\Activitylog\Models\Activity::where('causer_id', $user->id)
                            ->where('event', 'logout')
                            ->where('created_at', '>=', now()->subSeconds(10))
                            ->whereJsonContains('properties->ip', $request->ip())
                            ->whereJsonContains('properties->user_agent', $request->userAgent())
                            ->exists();
                            
                        if (!$recentLogout) {
                            // Log the logout activity only if no recent logout activity exists
                            try {
                                activity('authentication')
                                    ->causedBy($user)
                                    ->event('logout')
                                    ->withProperties([
                                        'ip' => $request->ip(),
                                        'user_agent' => $request->userAgent(),
                                        'logout_method' => 'force_logout',
                                        'user_id' => $user->id,
                                        'user_name' => $user->name
                                    ])
                                    ->log('User logged out of the system');
                            } catch (\Exception $e) {
                                Log::error('Failed to log force logout activity: ' . $e->getMessage());
                            }
                        }
                    }
                    
                    // Delete the specific token
                    $personalAccessToken->delete();
                }
            } catch (\Exception $e) {
                Log::error('Error in force logout: ' . $e->getMessage());
            }
        }
        
        return response()->json([
            'message' => 'Logout completed'
        ])->cookie(
            'auth_token', 
            null, 
            -1  // Expire the cookie immediately
        );
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'mobile' => 'required|unique:users|string|min:10|max:10',
            'email' => 'required|email|string|max:255|unique:users,email',
            'user_type' => 'required|string|in:user,admin,super_admin,developer',
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
                'mobile' => '0' . $request->mobile,
                'user_type' => $request->user_type,
                'password' => Hash::make('admin123')
            ]);

            if ($user) {
                // Log user registration
                activity('user_management')
                    ->causedBy($user)
                    ->performedOn($user)
                    ->withProperties([
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'registration_method' => 'api'
                    ])
                    ->log("registered a new account for {$user->name}");

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
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
