<?php

namespace App\DataTransferObjects;

class TripayCallbackData
{
    public function __construct(
        public readonly string $reference,
        public readonly string $merchantRef,
        public readonly string $status,
        public readonly string $signature,
        public readonly array $rawData,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            reference: $data['reference'] ?? '',
            merchantRef: $data['merchant_ref'] ?? '',
            status: $data['status'] ?? '',
            signature: $data['signature'] ?? '',
            rawData: $data,
        );
    }

    public function isValidSignature(string $privateKey): bool
    {
        $computedSignature = hash_hmac('sha256', $this->merchantRef . $this->status, $privateKey);
        return hash_equals($computedSignature, $this->signature);
    }

    public function getMappedStatus(): string
    {
        return match ($this->status) {
            'PAID' => 'paid',
            'EXPIRED' => 'expired',
            'FAILED', 'REFUND' => 'cancelled',
            default => 'pending',
        };
    }
}
