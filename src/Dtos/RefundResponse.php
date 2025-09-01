<?php

declare(strict_types=1);

namespace Vptrading\ChapaLaravel\Dtos;

final readonly class RefundResponse
{
    public function __construct(
        public string $status,
        public string $message,
        public ?array $data = []
    ) {
        //
    }
}
