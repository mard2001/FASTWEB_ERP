<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierRunningBalance extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tblSupplierRunningBalance';

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
        'supplier_code',
        'transaction_date',
        'ap_transaction_id',
        'transaction_type',
        'amount',
        'running_balance',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:2',
        'running_balance' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the accounts payable record associated with this transaction.
     */
    public function accountsPayable()
    {
        return $this->belongsTo(AccountsPayable::class, 'ap_transaction_id', 'id');
    }

    /**
     * Get the supplier associated with this running balance.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_code', 'SupplierCode');
    }

    /**
     * Scope to filter by supplier code.
     */
    public function scopeBySupplier($query, $supplierCode)
    {
        return $query->where('supplier_code', $supplierCode);
    }

    /**
     * Scope to filter by transaction type.
     */
    public function scopeByTransactionType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope to order by transaction date and creation time.
     */
    public function scopeOrderedByDate($query)
    {
        return $query->orderBy('transaction_date')
                    ->orderBy('created_at');
    }

    /**
     * Get the latest running balance for a supplier.
     */
    public static function getLatestBalance($supplierCode)
    {
        return static::where('supplier_code', $supplierCode)
                    ->orderBy('transaction_date', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->first()?->running_balance ?? 0;
    }

    /**
     * Get running balance history for a supplier.
     */
    public static function getBalanceHistory($supplierCode, $limit = null)
    {
        $query = static::where('supplier_code', $supplierCode)
                      ->orderBy('transaction_date')
                      ->orderBy('created_at');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }

    /**
     * Add a new running balance entry.
     */
    public static function addEntry($supplierCode, $transactionDate, $apTransactionId, $transactionType, $amount, $notes = null)
    {
        // Get the previous running balance
        $previousBalance = static::getLatestBalance($supplierCode);
        $newRunningBalance = $previousBalance + $amount;

        return static::create([
            'supplier_code' => $supplierCode,
            'transaction_date' => $transactionDate,
            'ap_transaction_id' => $apTransactionId,
            'transaction_type' => $transactionType,
            'amount' => $amount,
            'running_balance' => $newRunningBalance,
            'notes' => $notes
        ]);
    }

    /**
     * Recalculate running balances for a supplier from a specific date.
     */
    public static function recalculateFromDate($supplierCode, $fromDate)
    {
        $entries = static::where('supplier_code', $supplierCode)
                        ->where('transaction_date', '>=', $fromDate)
                        ->orderBy('transaction_date')
                        ->orderBy('created_at')
                        ->get();

        $runningBalance = static::where('supplier_code', $supplierCode)
                               ->where('transaction_date', '<', $fromDate)
                               ->orderBy('transaction_date', 'desc')
                               ->orderBy('created_at', 'desc')
                               ->first()?->running_balance ?? 0;

        foreach ($entries as $entry) {
            $runningBalance += $entry->amount;
            $entry->update(['running_balance' => $runningBalance]);
        }

        return $runningBalance;
    }
}