<?php

declare(strict_types=1);

namespace Vptrading\ChapaLaravel\Services;

use Illuminate\Support\Facades\Http;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Vptrading\ChapaLaravel\Dtos\AcceptPaymentResponse;
use Vptrading\ChapaLaravel\Dtos\RefundResponse;
use Vptrading\ChapaLaravel\Dtos\VerifyPaymentResponse;
use Vptrading\ChapaLaravel\Factories\AcceptPaymentResponseFactory;
use Vptrading\ChapaLaravel\ValueObjects\Customization;
use Vptrading\ChapaLaravel\ValueObjects\User;

class ChapaClient
{
    protected string $baseUrl;

    protected string $secretKey;

    protected string $apiVersion;

    public function __construct()
    {
        if (empty(config('chapa.secret_key'))) {
            throw new \InvalidArgumentException('Chapa secret key is not set.');
        }

        $apiVersion = (string) config('chapa.api_version', 'v1');

        if (! in_array($apiVersion, ['v1', 'v2'], true)) {
            throw new \InvalidArgumentException("Unsupported Chapa API version [{$apiVersion}]. Expected [v1] or [v2].");
        }

        $this->apiVersion = $apiVersion;
        $this->baseUrl = (string) config($apiVersion === 'v2' ? 'chapa.v2_base_url' : 'chapa.base_url');
        $this->secretKey = (string) config('chapa.secret_key');
    }

    public function acceptPayment(
        Money $amount,
        User $user,
        string $returnUrl,
        ?Customization $customization = null
    ): AcceptPaymentResponse {
        $currencies = new ISOCurrencies;

        $moneyFormatter = new DecimalMoneyFormatter($currencies);

        $txRef = config('chapa.ref_prefix').str()->random(10);

        $payload = $this->apiVersion === 'v2'
            ? [
                'amount' => $moneyFormatter->format($amount),
                'currency' => $amount->getCurrency()->getCode(),
                'merchant_reference' => $txRef,
                'customer' => [
                    'first_name' => $user->getFirstName(),
                    'last_name' => $user->getLastName(),
                    'email' => $user->getEmail(),
                    'phone_number' => $user->getPhoneNumber(),
                ],
            ]
            : [
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'amount' => $moneyFormatter->format($amount),
                'currency' => $amount->getCurrency()->getCode(),
                'email' => $user->getEmail(),
                'phone_number' => $user->getPhoneNumber(),
                'return_url' => $returnUrl,
                'callback_url' => route('chapa.webhook'),
                'tx_ref' => $txRef,
                'customization' => $customization?->toArray(),
            ];

        $endpoint = $this->apiVersion === 'v2' ? 'payments/hosted' : 'transaction/initialize';
        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/{$endpoint}", $payload);

        return AcceptPaymentResponseFactory::fromApiResponse($response->json(), $txRef);
    }

    public function verifyPayment(string $transactionId): VerifyPaymentResponse
    {
        $endpoint = $this->apiVersion === 'v2'
            ? "payments/{$transactionId}/verify"
            : "transaction/verify/{$transactionId}";
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/{$endpoint}");

        return new VerifyPaymentResponse(
            data: $response->json('data'),
            status: $response->json('status'),
            message: $response->json('message')
        );
    }

    public function refund($chapaRef, ?Money $amount = null, ?string $reason = null): RefundResponse
    {
        $currencies = new ISOCurrencies;

        $moneyFormatter = new DecimalMoneyFormatter($currencies);
        $payload = $this->apiVersion === 'v2'
            ? array_filter([
                'payment_reference' => $chapaRef,
                'amount' => $amount ? $moneyFormatter->format($amount) : null,
                'reason' => $reason,
            ], fn (mixed $value): bool => $value !== null)
            : [
                'amount' => $amount ? $moneyFormatter->format($amount) : null,
                'reason' => $reason,
            ];

        $endpoint = $this->apiVersion === 'v2' ? 'refunds' : "refund/{$chapaRef}";
        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/{$endpoint}", $payload);

        return new RefundResponse(
            status: $response->json('status'),
            message: $response->json('message'),
            data: $response->json('data')
        );
    }
}
