<?php

declare(strict_types=1);

namespace Vptrading\ChapaLaravel\ValueObjects;

final readonly class User
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public ?string $email,
        public string $phoneNumber,
    ) {
        //
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }
}
