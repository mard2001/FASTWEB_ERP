<?php

namespace App\Http\Requests\Shell;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\RequestValidator;

class StoreRRHeaderRequest extends FormRequest
{
    use RequestValidator;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $baseRules = [
            'SupplierCode' => 'required|string',
            'SupplierName' => 'required|string',
            'SupplierTIN' => 'required|string',
            'Address' => 'required|string',
            'RRNo' => 'required|numeric',
            'Date' => 'date',
            'Reference' => 'required|string',
            'Status' => 'required|string',
            'Total' => 'required|numeric',
            'ApprovedBy' => 'required|string',
            'CheckedBy' => 'required|string',
            'PreparedBy' => 'required|string',
            'PrintedBy' => 'required|string',
            'Items' => 'array',
        ];


        // Check if 'data' is present in the request
        $prefix = isset($this->data) ? 'data.' : '';

        // Adjust the rules based on the structure of the data
        return array_combine(
            array_map(fn($key) => $prefix . $key, array_keys($baseRules)),
            array_values($baseRules)
        );
    }
}
