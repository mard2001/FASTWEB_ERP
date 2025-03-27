<?php

namespace App\Http\Requests\Shell;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\RequestValidator;

class StoreInvoicesHeaderRequest extends FormRequest
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
            'SoldTo' => 'required|string',
            'totalAmount' => 'required|numeric',
            'totalVat' => 'required|numeric',
            'amountDue' => 'required|numeric',
            'invoiceNumber' => 'required|numeric',
            'accountNumber' => 'numeric',
            'dueDate' => 'required|date',
            'invoiceDate' => 'required|date',
            'paymentTerms' => 'string',
            'paymentMethod' => 'string',
            'deliveryMethod' => 'required|string',
            'yourTIN' => 'numeric',
            'paymentCollectedBy' => 'required|string',
            'bankDetails' => 'required|string',
            'shippedTo' => 'required|string',
            'plant' => 'required|string',
            'PONumber' => 'required|string',
            'Items' => 'nullable|array',
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
