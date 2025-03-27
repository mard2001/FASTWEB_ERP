<?php

namespace App\Http\Requests\Shell;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\RequestValidator;

class StoreInvoicesItemsRequest extends FormRequest
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
            'PRD_INDEX' => 'numeric',
            'totalPrice' => 'required|numeric',
            'pricePerUnit' => 'required|numeric',
            'UOM' => 'required|string',
            'subUOM' => 'required|string',
            'quantity' => 'required|numeric',
            'totalQuantityInUOM' => 'required|numeric',
            'orderDate' => 'required|date',
            'deliveryDate' => 'required|date',
            'productCode' => 'required|numeric',
            'deliveryNumber' => 'required|numeric',
            'orderNumber' => 'required|numeric',
            'discountPerUnit' => 'required|numeric',
            'totalDiscountPerUnit' => 'required|numeric',
            'netPricePerUnit' => 'required|numeric',
            'itemDescription' => 'required|string',
            'totalNetPrice' => 'required|numeric',
        ];

        // Check if 'data' is present in the request
        $prefix = isset($this->data) ? 'data.' : '*.';

        // Adjust the rules based on the structure of the data
        return array_combine(
            array_map(fn($key) => $prefix . $key, array_keys($baseRules)),
            array_values($baseRules)
        );

    }
}
