<?php

namespace App\DataTransferObjects;

class TripayPaymentResponse
{
    public function __construct(
        public readonly string $reference,
        public readonly string $merchantRef,
        public readonly string $paymentMethod,
        public readonly string $paymentName,
        public readonly ?string $payUrl,
        public readonly ?string $checkoutUrl,
        public readonly ?string $qrString,
        public readonly int $amount,
        public readonly int $expiredTime,
        public readonly array $rawResponse,
    ) {}

    public static function fromApiResponse(array $data): self
    {
        return new self(
            reference: $data['reference'] ?? '',
            merchantRef: $data['merchant_ref'] ?? '',
            paymentMethod: $data['payment_method'] ?? '',
            paymentName: $data['payment_name'] ?? '',
            payUrl: $data['pay_url'] ?? null,
            checkoutUrl: $data['checkout_url'] ?? null,
            qrString: $data['qr_string'] ?? null,
            amount: $data['amount'] ?? 0,
            expiredTime: $data['expired_time'] ?? 0,
            rawResponse: $data,
        );
    }

    public function toArray(): array
    {
        return [
            'tripay_reference' => $this->reference,
            'tripay_merchant_ref' => $this->merchantRef,
            'tripay_payment_method' => $this->paymentMethod,
            'tripay_payment_name' => $this->paymentName,
            'tripay_pay_url' => $this->payUrl,
            'tripay_checkout_url' => $this->checkoutUrl,
            'tripay_qr_string' => $this->qrString,
            'amount' => $this->amount,
        ];
    }
}
