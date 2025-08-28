<?php

declare(strict_types=1);

namespace Vptrading\ChapaLaravel\Services;

use Illuminate\Support\Facades\Http;
use Money\Money;
use Vptrading\ChapaLaravel\ValueObjects\UserValueObject;

class ChapaClient
{
    protected $baseUrl;

    protected $secretKey;

    public function __construct()
    {
        if (empty(config('chapa.secret_key'))) {
            throw new \InvalidArgumentException('Chapa secret key is not set.');
        }

        $this->baseUrl = config('chapa.base_url');
        $this->secretKey = config('chapa.secret_key');
    }

    public function acceptPayment(
        Money $amount,
        UserValueObject $user,
        string $returnUrl,
    ) {
        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/transaction/initialize", [
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'amount' => $amount->getAmount(),
                'currency' => $amount->getCurrency()->getCode(),
                'email' => $user->getEmail(),
                'phone_number' => $user->getPhoneNumber(),
                'return_url' => $returnUrl,
                'callback_url' => config('chapa.callback_url'),
                'tx_ref' => config('chapa.ref_prefix').str()->random(10),
            ]);

        return $response->json();
    }

    public function verifyPayment(string $transactionId)
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transaction/verify/{$transactionId}");

        return $response->json();
    }
}
