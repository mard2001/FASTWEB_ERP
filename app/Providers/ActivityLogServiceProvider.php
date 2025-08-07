<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Contracts\Activity;

class ActivityLogServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Listen to activity creation events to automatically add request information
        \App\Models\ActivityLog::saving(function (Activity $activity) {
            if (request()) {
                $existingProperties = $activity->properties ? $activity->properties : [];
                
                // Only add if not already present
                $defaultProperties = [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'url' => request()->fullUrl(),
                    'method' => request()->method(),
                ];

                // Merge with existing properties, giving precedence to explicitly set properties
                $mergedProperties = array_merge($defaultProperties, $existingProperties);
                
                $activity->properties = $mergedProperties;
            }
        });
    }
}
