<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Services\VipService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = Category::getDefault()?->id;
        $packageCodes = app(VipService::class)->getPackageCodes($categoryId);

        return [
            'package' => [
                'required',
                'string',
                Rule::in($packageCodes),
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
                'max:50',
                Rule::in(config('vip.payment.allowed_methods', [])),
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
            'payment_method.in' => 'Metode pembayaran tidak tersedia.',
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
