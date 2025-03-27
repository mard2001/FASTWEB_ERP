<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Otp extends Model
{
    use HasFactory;


    protected $fillable = ['mobile', 'otp', 'otp_expires_at'];

    /**
     * Check if the OTP is expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        return Carbon::now()->greaterThan($this->otp_expires_at);
    }

    /**
     * Get the user associated with this OTP.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
