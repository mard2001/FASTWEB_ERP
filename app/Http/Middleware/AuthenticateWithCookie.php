<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Auth;


class AuthenticateWithCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Retrieve token from the 'auth_token' cookie
        $token = $request->cookie('auth_token');
        
        $cookies = $request->cookies->all();
        return response()->json($cookies);
        

        if ($token) {
            return response()->json($token);
            //$user = Sanctum::findToken($token);  // This method checks the token

            try {   

                if ($user) {
                    Sanctum::actingAs($user);  // Log the user in
                } else {
                    //return redirect()->route('login');
                }
            } catch (\Exception $e) {
                //return redirect()->route('login');
            }
        } else {
            //return redirect()->route('login');
        }

        return $next($request);
    }
}
