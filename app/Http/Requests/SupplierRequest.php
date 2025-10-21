<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
{
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
        $supplierCode = $this->route('supplier') ?? null;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'data.SupplierCode' => [
                'required',
                'string',
                'max:10',
                $isUpdate ? Rule::unique('tblSupplier', 'SupplierCode')->ignore($supplierCode, 'SupplierCode') : 'unique:tblSupplier,SupplierCode'
            ],
            'data.SupplierName' => 'required|string|max:100',
            'data.SupplierType' => 'required|string|max:20',
            'data.ContactPerson' => 'required|string|max:100',
            'data.ContactNo' => 'required|string|max:11',
            'data.TermsCode' => 'required|string|max:10',
            'data.CompleteAddress' => 'required|string|max:100',
            'data.PostalCode' => 'nullable|string|max:10',
            'data.PriceCode' => 'required|integer|max:99',
            'data.CreditLimit' => 'nullable|numeric|min:0',
            'data.holdStatus' => 'required|string|max:1',
            'data.Region' => 'required|string|max:255',
            'data.Province' => 'required|string|max:255',
            'data.Municipality' => 'required|string|max:255',
            'data.City' => 'required|string|max:255',
            'data.Barangay' => 'required|string|max:255',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'data.SupplierCode.required' => 'Supplier Code is required.',
            'data.SupplierCode.unique' => 'Supplier Code is already taken, try another one.',
            'data.SupplierCode.max' => 'Supplier Code cannot exceed 10 characters.',
            'data.SupplierName.required' => 'Supplier Name is required.',
            'data.SupplierName.max' => 'Supplier Name cannot exceed 100 characters.',
            'data.SupplierType.required' => 'Supplier Type is required.',
            'data.SupplierType.max' => 'Supplier Type cannot exceed 20 characters.',
            'data.ContactPerson.required' => 'Contact Person is required.',
            'data.ContactPerson.max' => 'Contact Person cannot exceed 100 characters.',
            'data.ContactNo.required' => 'Contact Number is required.',
            'data.ContactNo.max' => 'Contact Number cannot exceed 11 characters.',
            'data.TermsCode.required' => 'Terms Code is required.',
            'data.TermsCode.max' => 'Terms Code cannot exceed 10 characters.',
            'data.CompleteAddress.required' => 'Complete Address is required.',
            'data.CompleteAddress.max' => 'Complete Address cannot exceed 100 characters.',
            'data.PostalCode.max' => 'Postal Code cannot exceed 10 characters.',
            'data.PriceCode.required' => 'Price Code is required.',
            'data.PriceCode.integer' => 'Price Code must be a valid integer.',
            'data.PriceCode.max' => 'Price Code cannot exceed 99.',
            'data.CreditLimit.numeric' => 'Credit Limit must be a valid number.',
            'data.CreditLimit.min' => 'Credit Limit cannot be negative.',
            'data.holdStatus.required' => 'Hold Status is required.',
            'data.holdStatus.max' => 'Hold Status cannot exceed 1 character.',
            'data.Region.required' => 'Region is required.',
            'data.Province.required' => 'Province is required.',
            'data.Municipality.required' => 'Municipality is required.',
            'data.City.required' => 'City is required.',
            'data.Barangay.required' => 'Barangay is required.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'data.SupplierCode' => 'Supplier Code',
            'data.SupplierName' => 'Supplier Name',
            'data.SupplierType' => 'Supplier Type',
            'data.ContactPerson' => 'Contact Person',
            'data.ContactNo' => 'Contact Number',
            'data.TermsCode' => 'Terms Code',
            'data.CompleteAddress' => 'Complete Address',
            'data.PostalCode' => 'Postal Code',
            'data.PriceCode' => 'Price Code',
            'data.CreditLimit' => 'Credit Limit',
            'data.holdStatus' => 'Hold Status',
            'data.Region' => 'Region',
            'data.Province' => 'Province',
            'data.Municipality' => 'Municipality',
            'data.City' => 'City',
            'data.Barangay' => 'Barangay',
        ];
    }
}