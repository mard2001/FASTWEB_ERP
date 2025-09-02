<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\ActivityLoggable;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, ActivityLoggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile',
        'user_type',
        'status',
        'deactivation_reason'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'status' => 'integer',
    ];

    /**
     * Get the activity log options for this model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'mobile', 'user_type'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('user_management');
    }

    /**
     * Get the log name for activity logging.
     */
    protected function getLogName(): string
    {
        return 'user_management';
    }

    /**
     * Get the attributes to log for activity.
     */
    protected function getLogAttributes(): array
    {
        return ['name', 'email', 'mobile', 'user_type'];
    }

    /**
     * Get the log description for different events.
     */
    protected function getLogDescription(string $eventName): string
    {
        $descriptions = [
            'created' => "created user account for {$this->name}",
            'updated' => "updated user account for {$this->name}",
            'deleted' => "deleted user account for {$this->name}",
            'login' => "logged into the system",
            'logout' => "logged out of the system",
            'activated' => "activated user account for {$this->name}",
            'deactivated' => "deactivated user account for {$this->name}",
        ];

        return $descriptions[$eventName] ?? "{$eventName} user {$this->name}";
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query to only include inactive users.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 0);
    }

    /**
     * Check if user is active.
     */
    public function isActive()
    {
        return $this->status == 1;
    }

    /**
     * Activate the user.
     */
    public function activate()
    {
        $this->update([
            'status' => 1,
            'deactivation_reason' => null
        ]);
    }

    /**
     * Deactivate the user.
     */
    public function deactivate($reason = null)
    {
        $this->update([
            'status' => 0,
            'deactivation_reason' => $reason
        ]);
    }
}
