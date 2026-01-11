<?php

namespace App\Exceptions;

use Exception;

class TripayException extends Exception
{
    public static function notConfigured(): self
    {
        return new self('Tripay is not configured. Please set TRIPAY_API_KEY, TRIPAY_PRIVATE_KEY, and TRIPAY_MERCHANT_CODE in .env file.');
    }

    public static function invalidSignature(): self
    {
        return new self('Invalid Tripay callback signature. Possible security breach attempt.');
    }

    public static function paymentCreationFailed(string $message): self
    {
        return new self("Failed to create payment: {$message}");
    }

    public static function channelsFetchFailed(string $message): self
    {
        return new self("Failed to fetch payment channels: {$message}");
    }

    public static function paymentNotFound(string $reference): self
    {
        return new self("Payment not found for reference: {$reference}");
    }

    public static function invalidPackage(string $package): self
    {
        return new self("Invalid package selected: {$package}");
    }

    public static function callbackProcessingFailed(string $message): self
    {
        return new self("Failed to process Tripay callback: {$message}");
    }
}
