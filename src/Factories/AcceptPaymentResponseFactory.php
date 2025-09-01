<?php

declare(strict_types=1);

namespace Vptrading\ChapaLaravel\Factories;

use Vptrading\ChapaLaravel\Dtos\AcceptPaymentResponse;

class AcceptPaymentResponseFactory
{
    public static function fromApiResponse(array $response): AcceptPaymentResponse
    {
        if (array_key_exists('message', $response)) {
            if (is_string($response['message'])) {
                $message = $response['message'];
            } elseif (is_array($response['message'])) {
                $validationErrors = $response['message'];
            }
        }

        return new AcceptPaymentResponse(
            checkout_url: $response['data']['checkout_url'] ?? null,
            status: $response['status'] ?? 'unknown',
            message: $message ?? null,
            validation_errors: $validationErrors ?? []
        );
    }
}
