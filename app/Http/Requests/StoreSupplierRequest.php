<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255', 'unique:suppliers,supplier_name'],
            'business_type' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', 'unique:suppliers,email'],
            'contact_person' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:50'],
            'contact_email' => ['required', 'email', 'max:255'],
            'lead_time' => ['required', 'string'],
            'delivery_schedule' => ['required', 'string'],
            'moq' => ['required', 'string'],
            'products' => ['required', 'array', 'min:1'],
            'specified_product' => [
                'nullable',
                Rule::requiredIf(function () {
                    return is_array($this->input('products')) && in_array('Others', $this->input('products'));
                }),
                'string',
                'max:255',
            ],
            'payment_terms' => ['required', 'string'],
            'payment_method' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'company_name.unique' => 'A supplier with this company name already exists.',
            'email.unique' => 'A supplier with this email address already exists.',
            'specified_product.required' => 'Please specify the product type when Others is selected.',
            'specified_product.required_if' => 'Please specify the product type when Others is selected.',
        ];
    }
}
