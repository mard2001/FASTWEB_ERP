<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditMemoApplication extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tblCreditMemoApplications';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'source_ap_id',
        'target_ap_id',
        'credit_amount',
        'application_date',
        'created_by',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'credit_amount' => 'decimal:2',
        'application_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the source accounts payable record that generated the credit.
     */
    public function sourceAccountsPayable()
    {
        return $this->belongsTo(AccountsPayable::class, 'source_ap_id', 'id');
    }

    /**
     * Get the target accounts payable record that received the credit.
     */
    public function targetAccountsPayable()
    {
        return $this->belongsTo(AccountsPayable::class, 'target_ap_id', 'id');
    }



    /**
     * Get the user who created this credit memo application.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Scope to filter by source AP ID.
     */
    public function scopeBySourceAP($query, $sourceApId)
    {
        return $query->where('source_ap_id', $sourceApId);
    }

    /**
     * Scope to filter by target AP ID.
     */
    public function scopeByTargetAP($query, $targetApId)
    {
        return $query->where('target_ap_id', $targetApId);
    }

    /**
     * Scope to filter by supplier code through source AP.
     */
    public function scopeBySupplierCode($query, $supplierCode)
    {
        return $query->whereHas('sourceAccountsPayable', function ($q) use ($supplierCode) {
            $q->where('supplier_code', $supplierCode);
        });
    }

    /**
     * Get the total credit amount for a specific source AP.
     */
    public static function getTotalCreditGenerated($sourceApId)
    {
        return static::where('source_ap_id', $sourceApId)->sum('credit_amount');
    }

    /**
     * Get the total credit amount for a specific target AP.
     */
    public static function getTotalCreditReceived($targetApId)
    {
        return static::where('target_ap_id', $targetApId)->sum('credit_amount');
    }
}