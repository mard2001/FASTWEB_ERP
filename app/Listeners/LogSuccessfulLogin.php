<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $user = $event->user;
        $request = request();

        activity('authentication')
            ->causedBy($user instanceof \Illuminate\Database\Eloquent\Model ? $user : $user->getAuthIdentifier())
            ->event('login')
            ->withProperties([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_method' => 'web',
                'guard' => $event->guard,
                'remember' => $event->remember,
                'timestamp' => now()->toISOString()
            ])
            ->log('User logged into the system');
    }
}
