<?php

namespace App\Observers;

use App\Models\Orders\PO;

class POObserver
{

    public function creating(PO $po)
    {
        $po->PONumber = $this->generateNumber('PONumber', 'SO');
        $po->OrderNumber = $this->generateNumber('OrderNumber', 'ON');
        $po->PODate = now()->format('Y-m-d');
    }

    public function updating(PO $po)
    {
        // no update observer
        $po->PODate = now()->format('Y-m-d');

    }

    public function updated(PO $po)
    {
        // Update total cost after PO is updated
        $po->updateTotalCost();
    }

    public function created(PO $po)
    {
        // Update total cost after PO is created
        $po->updateTotalCost();
    }

    public function deleted(PO $po)
    {
        $po->POItems()->delete();        
    }

    private function generateNumber(string $field, string $prefix): string
    {
        $year = date('y');
        $lastRecord = PO::orderByDesc('DateUploaded')->first();

        $sequence = $lastRecord
            ? (int) substr($lastRecord->$field, 5) + 1
            : 1;

        return sprintf('%s-%s%07d', $prefix, $year, $sequence);
    }
}
