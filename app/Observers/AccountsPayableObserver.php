<?php

namespace App\Observers;

use App\Models\AccountsPayable;
use App\Models\SupplierCredit;
use Illuminate\Support\Facades\Log;

class AccountsPayableObserver
{
    /**
     * Handle the AccountsPayable "created" event.
     */
    public function created(AccountsPayable $accountsPayable): void
    {
        $this->updateSupplierCredit($accountsPayable);
    }

    /**
     * Handle the AccountsPayable "updated" event.
     */
    public function updated(AccountsPayable $accountsPayable): void
    {
        $this->updateSupplierCredit($accountsPayable);
    }

    /**
     * Handle the AccountsPayable "deleted" event.
     */
    public function deleted(AccountsPayable $accountsPayable): void
    {
        $this->updateSupplierCredit($accountsPayable);
    }

    /**
     * Update supplier credit data for the affected supplier
     */
    private function updateSupplierCredit(AccountsPayable $accountsPayable): void
    {
        try {
            if ($accountsPayable->supplier_code) {
                SupplierCredit::updateSupplierCredit(trim($accountsPayable->supplier_code));
                Log::info('SupplierCredit updated via observer for supplier: ' . trim($accountsPayable->supplier_code), [
                    'ap_id' => $accountsPayable->id,
                    'event' => 'accounts_payable_observer'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error updating supplier credit via observer: ' . $e->getMessage(), [
                'supplier_code' => $accountsPayable->supplier_code,
                'ap_id' => $accountsPayable->id,
                'exception' => $e
            ]);
        }
    }
}