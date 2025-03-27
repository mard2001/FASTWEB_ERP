<?php

namespace App\Http\Requests\Orders;

use Illuminate\Support\Facades\Validator;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\RequestValidator;

class StorePOHeaderRequest extends FormRequest
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

        $baseRules =  [
            'PODate' => 'date_format:Y-m-d',
            'SupplierCode' => 'required|string',
            'SupplierName' => 'required|string',
            'productType' => 'string',
            'orderPlacer' => 'required|string',
            'FOB' => 'string',
            'orderPlacerEmail' => 'string',
            'deliveryAddress' => 'required|string',
            'deliveryMethod' => 'required|string',
            'totalNetVol' => 'string',
            'volumeUOM' => 'string',
            'totalNetWeight' => 'string',
            'totalGrossWeight' => 'string',
            'weightUOM' => 'string',
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
