<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'package' => [
                'required',
                'string',
                'in:1day,3days,7days,30days'
            ],
            'telegram_user_id' => [
                'required',
                'numeric',
                'digits_between:1,20'
            ],
            'username' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9_]+$/'
            ],
            'first_name' => [
                'required',
                'string',
                'max:255'
            ],
            'last_name' => [
                'nullable',
                'string',
                'max:255'
            ],
            'payment_method' => [
                'required',
                'string',
                'max:50'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'package.required' => 'Paket VIP wajib dipilih.',
            'package.in' => 'Paket VIP tidak valid.',
            'telegram_user_id.required' => 'Telegram User ID wajib diisi.',
            'telegram_user_id.numeric' => 'Telegram User ID harus berupa angka.',
            'telegram_user_id.digits_between' => 'Telegram User ID tidak valid.',
            'username.regex' => 'Username Telegram hanya boleh mengandung huruf, angka, dan underscore.',
            'first_name.required' => 'Nama depan wajib diisi.',
            'first_name.max' => 'Nama depan maksimal 255 karakter.',
            'last_name.max' => 'Nama belakang maksimal 255 karakter.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Remove @ from username if exists
        if ($this->username) {
            $this->merge([
                'username' => ltrim($this->username, '@')
            ]);
        }
    }
}
