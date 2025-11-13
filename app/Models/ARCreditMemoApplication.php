<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ARCreditMemoApplication extends Model
{
    use HasFactory;

    protected $table = 'tblARCreditMemoApplications';

    protected $primaryKey = 'id';

    protected $fillable = [
        'source_ar_id',
        'target_ar_id',
        'credit_amount',
        'application_date',
        'created_by',
        'notes',
        'status',
    ];

    protected $casts = [
        'credit_amount' => 'decimal:2',
        'application_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Source Accounts Receivable record (generated the credit).
     */
    public function sourceAccountsReceivable()
    {
        return $this->belongsTo(AccountsReceivable::class, 'source_ar_id', 'id');
    }

    /**
     * Target Accounts Receivable record (received the credit).
     */
    public function targetAccountsReceivable()
    {
        return $this->belongsTo(AccountsReceivable::class, 'target_ar_id', 'id');
    }

    /**
     * Creator user.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Scope by source AR ID.
     */
    public function scopeBySourceAR($query, $sourceArId)
    {
        return $query->where('source_ar_id', $sourceArId);
    }

    /**
     * Scope by target AR ID.
     */
    public function scopeByTargetAR($query, $targetArId)
    {
        return $query->where('target_ar_id', $targetArId);
    }

    /**
     * Total credit generated for a source AR.
     */
    public static function getTotalCreditGenerated($sourceArId)
    {
        return static::where('source_ar_id', $sourceArId)->sum('credit_amount');
    }

    /**
     * Total credit received for a target AR.
     */
    public static function getTotalCreditReceived($targetArId)
    {
        return static::where('target_ar_id', $targetArId)->sum('credit_amount');
    }
}