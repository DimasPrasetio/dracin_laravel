<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TripayCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference' => 'required|string',
            'merchant_ref' => 'required|string',
            'status' => 'required|string',
            'signature' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'reference.required' => 'Reference is required.',
            'merchant_ref.required' => 'Merchant reference is required.',
            'status.required' => 'Payment status is required.',
            'signature.required' => 'Signature is required.',
        ];
    }
}
