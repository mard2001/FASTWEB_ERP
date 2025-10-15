<?php

namespace App\Observers;

use App\Models\Supplier;
use App\Models\SupplierCredit;
use Illuminate\Support\Facades\Log;

class SupplierObserver
{
    /**
     * Handle the Supplier "created" event.
     */
    public function created(Supplier $supplier): void
    {
        $this->updateSupplierCredit($supplier);
    }

    /**
     * Handle the Supplier "updated" event.
     */
    public function updated(Supplier $supplier): void
    {
        $this->updateSupplierCredit($supplier);
    }

    /**
     * Update supplier credit data for the new/updated supplier
     */
    private function updateSupplierCredit(Supplier $supplier): void
    {
        try {
            if ($supplier->SupplierCode) {
                SupplierCredit::updateSupplierCredit(trim($supplier->SupplierCode));
                Log::info('SupplierCredit updated via observer for new/updated supplier: ' . trim($supplier->SupplierCode), [
                    'supplier_name' => $supplier->SupplierName,
                    'event' => 'supplier_observer'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error updating supplier credit via supplier observer: ' . $e->getMessage(), [
                'supplier_code' => $supplier->SupplierCode,
                'supplier_name' => $supplier->SupplierName,
                'exception' => $e
            ]);
        }
    }
}