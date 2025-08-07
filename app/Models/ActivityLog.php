<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity;

class ActivityLog extends Activity
{
    /**
     * The table associated with the model.
     */
    protected $table = 'activity_log';

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'properties' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user name attribute.
     */
    public function getUserNameAttribute()
    {
        if ($this->causer) {
            return $this->causer->name;
        }
        
        return 'System';
    }

    /**
     * Get formatted properties for display.
     */
    public function getFormattedPropertiesAttribute()
    {
        $properties = $this->properties;
        
        if (!$properties) {
            return [
                'ip' => 'Unknown',
                'user_agent' => 'Unknown',
                'attributes' => [],
                'old' => [],
            ];
        }

        return [
            'ip' => $properties['ip'] ?? 'Unknown',
            'user_agent' => $properties['user_agent'] ?? 'Unknown',
            'url' => $properties['url'] ?? 'Unknown',
            'method' => $properties['method'] ?? 'Unknown',
            'attributes' => $properties['attributes'] ?? [],
            'old' => $properties['old'] ?? [],
        ];
    }

    /**
     * Get the IP address from properties.
     */
    public function getIpAddressAttribute()
    {
        $properties = $this->properties;
        return is_array($properties) ? ($properties['ip'] ?? 'Unknown') : 'Unknown';
    }

    /**
     * Get the activity description with context.
     */
    public function getFullDescriptionAttribute()
    {
        $userName = $this->user_name;
        $description = $this->description;
        
        return "{$userName} {$description}";
    }

    /**
     * Get activities for today.
     */
    public static function today()
    {
        return static::whereDate('created_at', today());
    }

    /**
     * Get activities for this week.
     */
    public static function thisWeek()
    {
        return static::whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Get activities for this month.
     */
    public static function thisMonth()
    {
        return static::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    /**
     * Get login activities.
     */
    public static function logins()
    {
        return static::where('event', 'login');
    }

    /**
     * Get logout activities.
     */
    public static function logouts()
    {
        return static::where('event', 'logout');
    }

    /**
     * Clean up old activity logs.
     */
    public static function cleanup($days = 90)
    {
        return static::where('created_at', '<', now()->subDays($days))->delete();
    }
}
