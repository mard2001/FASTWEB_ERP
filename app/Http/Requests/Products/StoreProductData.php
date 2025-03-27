<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductData extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        
            $baseRules = [
                'StockCode' => 'required|string',
                'Description' => 'required|string',
                'LongDesc' => 'required|string',
                'AlternateKey1' => 'required|string',
                'StockUom' => 'required|string',
                'AlternateUom' => 'required|string',
                'OtherUom' => 'required|string',
                'ConvFactAltUom' => 'required|string',
                'ConvFactOthUom' => 'required|string',
                'Mass' => 'required|string',
                'Volume' => 'required|string',
                'ProductClass' => 'required|string',
                'WarehouseToUse' => 'required|string',
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
