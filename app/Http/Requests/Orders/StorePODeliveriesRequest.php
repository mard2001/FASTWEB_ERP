<?php

namespace App\Http\Requests\Shell;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\RequestValidator;

class StorePODeliveriesRequest extends FormRequest
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
            // Validate that the input is an array
            // '*' => 'required|array',
            // Validate each field in the array
            'PRD_INDEX' => 'numeric',
            'PONumber' => 'required|string',
            'MaterialCode' => 'required|numeric',
            'Decription' => 'required|string',
            'Quantity' => 'required|numeric',
            'UOM' => 'required|string',
            'ItemVolume' => 'required|numeric',
            'ItemVolumeUOM' => 'string',
            'ItemWeight' => 'required|numeric',
            'ItemWeightUOM' => 'string',
            'ShippingDate' => 'required|date',
            'DeliveryDate' => 'required|date',
            'DeliveryNumber' => 'required|numeric',
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
