<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogAuthenticationActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Log successful login attempts
        if ($request->is('api/auth/login') && $response->isSuccessful()) {
            $user = Auth::user();
            if ($user instanceof \Illuminate\Database\Eloquent\Model) {
                activity('authentication')
                    ->causedBy($user)
                    ->event('login')
                    ->withProperties([
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'login_method' => 'api',
                        'route' => $request->route()->getName(),
                        'timestamp' => now()->toISOString()
                    ])
                    ->log('User logged into the system via API');
            }
        }

        // Log successful logout attempts
        if ($request->is('api/auth/logout') && $response->isSuccessful()) {
            $user = Auth::user();
            if ($user instanceof \Illuminate\Database\Eloquent\Model) {
                activity('authentication')
                    ->causedBy($user)
                    ->event('logout')
                    ->withProperties([
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'logout_method' => 'api',
                        'route' => $request->route()->getName(),
                        'timestamp' => now()->toISOString()
                    ])
                    ->log('User logged out of the system via API');
            }
        }

        return $response;
    }
}
