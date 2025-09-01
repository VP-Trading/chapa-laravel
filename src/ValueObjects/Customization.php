<?php

declare(strict_types=1);

namespace Vptrading\ChapaLaravel\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

final readonly class Customization implements Arrayable
{
    public function __construct(
        public string $title,
        public ?string $description = null,
        public ?string $logo = null
    ) {}

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'logo' => $this->logo,
        ];
    }
}
