<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TripayCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $reference = $this->input('reference');
        $merchantRef = $this->input('merchant_ref');
        $note = (string) $this->input('note', '');
        $hasCallbackEvent = $this->hasHeader('X-Callback-Event');

        $isTestCallback = (
            is_null($reference) &&
            is_null($merchantRef) &&
            (str_contains($note, 'Test') || str_contains($note, 'test')) &&
            $hasCallbackEvent
        );

        $this->merge([
            'is_test_callback' => $isTestCallback,
            'header_signature' => $this->header('X-Callback-Signature'),
        ]);
    }

    public function rules(): array
    {
        return [
            'reference' => 'required_unless:is_test_callback,true|string',
            'merchant_ref' => 'required_unless:is_test_callback,true|string',
            'status' => 'required_unless:is_test_callback,true|string',
            'signature' => 'required_without:header_signature|string',
            'note' => 'nullable|string',
            'is_test_callback' => 'boolean',
            'header_signature' => 'nullable|string',
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

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Invalid callback payload',
            'errors' => $validator->errors(),
        ], 400));
    }
}
