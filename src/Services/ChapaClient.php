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
        User $user,
        string $returnUrl,
        ?Customization $customization = null
    ): AcceptPaymentResponse {
        $currencies = new ISOCurrencies;

        $moneyFormatter = new DecimalMoneyFormatter($currencies);

        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/transaction/initialize", [
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'amount' => $moneyFormatter->format($amount),
                'currency' => $amount->getCurrency()->getCode(),
                'email' => $user->getEmail(),
                'phone_number' => $user->getPhoneNumber(),
                'return_url' => $returnUrl,
                'callback_url' => route('chapa.webhook'),
                'tx_ref' => config('chapa.ref_prefix').str()->random(10),
                'customization' => $customization?->toArray(),
            ]);

        return AcceptPaymentResponseFactory::fromApiResponse($response->json());
    }

    public function verifyPayment(string $transactionId): VerifyPaymentResponse
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transaction/verify/{$transactionId}");

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
        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/refund/$chapaRef", [
                'amount' => $amount ? $moneyFormatter->format($amount) : null,
                'reason' => $reason,
            ]);

        return new RefundResponse(
            status: $response->json('status'),
            message: $response->json('message'),
            data: $response->json('data')
        );
    }
}
