<?php

namespace App\Observers;

use App\Models\Salesman\Salesperson;

class SalespersonObserver
{
    public function creating(Salesperson $so)
    {
        $so->PONumber = $this->generateNumber('EmployeeID', 'EMP');
        $so->PODate = now()->format('Y-m-d');
    }

    private function generateNumber(string $field, string $prefix): string
    {
        $year = date('y');
        $lastRecord = Salesperson::orderByDesc('EmployeeID')->first();

        $sequence = $lastRecord
            ? (int) substr($lastRecord->$field, 3) + 1
            : 1;

        return sprintf('%s-%s%07d', $prefix, $year, $sequence);
    }
}
