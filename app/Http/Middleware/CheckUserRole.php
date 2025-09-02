<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
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
        
        if (!$token) {
            // For web routes, redirect to login instead of JSON response
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }
        
        try {
            // Find the token and get the user
            $personalAccessToken = PersonalAccessToken::findToken($token);
            
            if (!$personalAccessToken || !$personalAccessToken->tokenable) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Invalid token'], 401);
                }
                return redirect()->route('login')->with('error', 'Invalid authentication token.');
            }
            
            $user = $personalAccessToken->tokenable;
            
            // Check if user has required role
            if (!in_array($user->user_type, $roles)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Access denied. Required roles: ' . implode(', ', $roles),
                        'user_role' => $user->user_type
                    ], 403);
                }
                return redirect()->back()->with('error', 'Access denied. You do not have permission to access this page.');
            }
            
            // Add user to request for later use
            $request->merge(['authenticated_user' => $user]);
            
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Authentication failed'], 401);
            }
            return redirect()->route('login')->with('error', 'Authentication failed. Please log in again.');
        }
        
        return $next($request);
    }
}