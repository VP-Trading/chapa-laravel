<?php

declare(strict_types=1);

namespace Vptrading\ChapaLaravel\Dtos;

final readonly class VerifyPaymentResponse
{
    public function __construct(
        public ?array $data,
        public string $status,
        public string $message
    ) {
        //
    }
}
