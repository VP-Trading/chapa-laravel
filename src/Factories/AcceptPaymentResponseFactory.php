<?php

declare(strict_types=1);

namespace Vptrading\ChapaLaravel\Factories;

use Vptrading\ChapaLaravel\Dtos\AcceptPaymentResponse;

class AcceptPaymentResponseFactory
{
    public static function fromApiResponse(array $response, string $transactionId): AcceptPaymentResponse
    {
        if (array_key_exists('message', $response)) {
            if (is_string($response['message'])) {
                $message = $response['message'];
            } elseif (is_array($response['message'])) {
                $validationErrors = $response['message'];
            }
        }

        if (isset($response['error']['details'])) {
            $validationErrors = is_array($response['error']['details'])
                ? $response['error']['details']
                : ['error' => $response['error']['details']];
        }

        return new AcceptPaymentResponse(
            checkout_url: $response['data']['checkout_url'] ?? null,
            status: $response['status'] ?? 'unknown',
            message: $message ?? null,
            transaction_id: $transactionId,
            validation_errors: $validationErrors ?? []
        );
    }
}
