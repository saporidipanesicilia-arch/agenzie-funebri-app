<?php

namespace App\Http\Requests\Api\Funeral;

use Illuminate\Foundation\Http\FormRequest;

/**
 * HTTP validation for storing a new funeral from wizard.
 * 
 * This contains ONLY framework-level validation (types, formats, required fields).
 * Business rules validation happens in the Use Case.
 */
class StoreFuneralHttpRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled by middleware
        return true;
    }

    public function rules(): array
    {
        return [
            // Step 1: Deceased Data
            'deceased_name' => ['required', 'string', 'max:255'],
            'deceased_surname' => ['required', 'string', 'max:255'],
            'deceased_tax_code' => ['required', 'string', 'size:16', 'regex:/^[A-Z0-9]{16}$/'],
            'deceased_birth_date' => ['required', 'date'],
            'deceased_birth_city' => ['required', 'string', 'max:255'],
            'deceased_death_date' => ['required', 'date'],
            'deceased_death_city' => ['required', 'string', 'max:255'],

            // Step 2: Ceremony Configuration
            'ceremony_type' => ['required', 'in:burial,cremation'],
            'ceremony_location' => ['nullable', 'string', 'max:500'],
            'ceremony_date' => ['nullable', 'date'],

            // Step 3: Products
            'product_ids' => ['sometimes', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],

            // Step 4: Documents
            'required_documents' => ['sometimes', 'array'],
            'required_documents.*' => ['string', 'in:certificate_death,cremation_request,burial_permit,identity_document'],

            // Optional
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'deceased_tax_code.regex' => 'Il codice fiscale deve essere in formato valido (16 caratteri alfanumerici).',
            'ceremony_type.in' => 'Il tipo di cerimonia deve essere "sepoltura" o "cremazione".',
        ];
    }
}
