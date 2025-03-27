<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\RequestValidator;

class StorePOItemsRequest extends FormRequest
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
            'PRD_INDEX' => 'required|numeric',
            'PONumber' => 'required|string',
            'StockCode' => 'required|string',
            'Decription' => 'required|string',
            'Quantity' => 'required|numeric',
            'UOM' => 'required|string',
            'DeliveredQuantity' => 'numeric',
            'ItemVolume' => 'numeric',
            'ItemVolumeUOM' => 'string',
            'ItemWeight' => 'numeric',
            'ItemWeightUOM' => 'string',
            'TotalPrice' => 'required|numeric',
            'UsedCurrency' => 'string',
            'PricePerUnit' => 'required|numeric',
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
