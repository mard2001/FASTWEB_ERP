<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogSuccessfulLogout
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
     * @param  \Illuminate\Auth\Events\Logout  $event
     * @return void
     */
    public function handle(Logout $event)
    {
        $user = $event->user;
        $request = request();

        // Ensure $user is an Eloquent model instance
        $modelUser = ($user instanceof \Illuminate\Database\Eloquent\Model) ? $user : null;

        if ($modelUser) {
            activity('authentication')
                ->causedBy($modelUser)
                ->event('logout')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'logout_method' => 'web',
                    'guard' => $event->guard,
                    'timestamp' => now()->toISOString()
                ])
                ->log('User logged out of the system');
        }
    }
}
