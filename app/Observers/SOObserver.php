<?php

namespace App\Observers;

use App\Models\SalesOrder\SOMaster;

class SOObserver
{
    public function creating(SOMaster $so)
    {
        $so->SalesOrder = $this->generateNumber('SalesOrder', 'SO');
        // $so->PODate = now()->format('Y-m-d');
    }

    private function generateNumber(string $field, string $prefix): string
    {
        $year = date('y');
        $lastRecord = SOMaster::orderByDesc('SalesOrder')->first();
        $sequence = $lastRecord ? (int) substr($lastRecord->$field, 5) + 1 : 1;

        return sprintf('%s%s%07d', $prefix, $year, $sequence);
    }
}
