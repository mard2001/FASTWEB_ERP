<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get token from Authorization header or cookie
        $token = null;
        
        // Check Authorization header first
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
        }
        
        // If no token in header, check cookie
        if (!$token) {
            $cookieToken = $request->cookie('auth_token');
            if ($cookieToken) {
                // Decode URL-encoded cookie value
                $token = urldecode($cookieToken);
            }
        }
        
        if ($token) {
            try {
                // Find the token and get the user
                $personalAccessToken = PersonalAccessToken::findToken($token);
                
                if ($personalAccessToken && $personalAccessToken->tokenable) {
                    $user = $personalAccessToken->tokenable;
                    
                    // Check if user account is still active
                    if (!$user->isActive()) {
                        // Delete the token to force logout
                        $personalAccessToken->delete();
                        
                        // Clear auth cookie if it exists
                        $response = redirect()->route('account.deactivated')
                            ->withCookie(cookie()->forget('auth_token'))
                            ->with([
                                'mobile' => $user->mobile,
                                'deactivation_reason' => $user->deactivation_reason,
                                'deactivation_date' => $user->updated_at
                            ]);
                        
                        // For API requests, return JSON response
                        if ($request->expectsJson()) {
                            return response()->json([
                                'message' => 'Your account has been deactivated. Please contact your administrator.',
                                'status' => 'deactivated',
                                'mobile' => $user->mobile,
                                'deactivation_reason' => $user->deactivation_reason,
                                'deactivation_date' => $user->updated_at
                            ], 403);
                        }
                        
                        // For web requests, redirect to deactivated page with parameters
                        $queryParams = http_build_query([
                            'mobile' => $user->mobile,
                            'reason' => $user->deactivation_reason,
                            'date' => $user->updated_at->format('Y-m-d H:i:s')
                        ]);
                        
                        return redirect()->to('/account-deactivated?' . $queryParams);
                    }
                }
            } catch (\Exception $e) {
                // If there's an error with token validation, let other middleware handle it
            }
        }
        
        return $next($request);
    }
}