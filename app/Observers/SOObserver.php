<?php

namespace App\Observers;

use App\Models\SalesOrder\SOMaster;

class SOObserver
{
    public function creating(SOMaster $so)
    {
        $so->SalesOrder = $this->generateNumber('SalesOrder', 'SO');
        $so->CustomerPoNumber = $this->generateTestPONumber($so->Salesperson);
        // $so->PODate = now()->format('Y-m-d');
    }

    private function generateNumber(string $field, string $prefix): string
    {
        $year = date('y');
        $lastRecord = SOMaster::orderByDesc('SalesOrder')->first();
        $sequence = $lastRecord ? (int) substr($lastRecord->$field, 5) + 1 : 1;

        return sprintf('%s%s%07d', $prefix, $year, $sequence);
    }

    private function generateTestPONumber(string $prefix): string
    {
        $dateCode = date('ymd'); 
        $sequence = rand('258736213', '259999999');

        return sprintf('%s%s%s-%s', $prefix, 'SO', $dateCode, $sequence);
    }
}
