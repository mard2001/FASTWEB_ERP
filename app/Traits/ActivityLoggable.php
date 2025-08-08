<?php

namespace App\Traits;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

trait ActivityLoggable
{
    use LogsActivity;

    /**
     * Get the options for activity logging.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->getLogAttributes())
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => $this->getLogDescription($eventName))
            ->useLogName($this->getLogName());
    }

    /**
     * Get the attributes to log.
     */
    protected function getLogAttributes(): array
    {
        // Default to fillable attributes, can be overridden in models
        return $this->fillable ?? ['*'];
    }

    /**
     * Get the log description for different events.
     */
    protected function getLogDescription(string $eventName): string
    {
        $modelName = class_basename($this);
        
        $descriptions = [
            'created' => "Created a new {$modelName}",
            'updated' => "Updated {$modelName}",
            'deleted' => "Deleted {$modelName}",
            'confirmed' => "Confirmed {$modelName}",
        ];

        return $descriptions[$eventName] ?? "{$eventName} {$modelName}";
    }

    /**
     * Set the log name for this model.
     */
    protected function getLogName(): string
    {
        return strtolower(class_basename($this));
    }

    /**
     * Log a custom activity.
     */
    public function logActivity(string $description, array $properties = [], string $event = 'custom')
    {
        // Always include IP address and other request information
        $defaultProperties = [];
        if (request()) {
            $defaultProperties = [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
            ];
        }

        // Merge with provided properties
        $mergedProperties = array_merge($defaultProperties, $properties);

        activity($this->getLogName())
            ->performedOn($this)
            ->withProperties($mergedProperties)
            ->event($event)
            ->log($description);
    }
}
