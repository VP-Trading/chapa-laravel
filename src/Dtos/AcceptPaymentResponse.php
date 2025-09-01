<?php

declare(strict_types=1);

namespace Vptrading\ChapaLaravel\Dtos;

final readonly class AcceptPaymentResponse
{
    public function __construct(
        public ?string $checkout_url,
        public string $status,
        public ?string $message = null,
        public array $validation_errors = []
    ) {
        //
    }
}
