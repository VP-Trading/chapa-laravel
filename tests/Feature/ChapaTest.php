<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Money\Money;
use Vptrading\ChapaLaravel\Dtos\AcceptPaymentResponse;
use Vptrading\ChapaLaravel\Dtos\RefundResponse;
use Vptrading\ChapaLaravel\Dtos\VerifyPaymentResponse;
use Vptrading\ChapaLaravel\Facades\Chapa;
use Vptrading\ChapaLaravel\ValueObjects\User;

it('accepts payments', function (): void {
    config()->set('chapa.secret_key', 'test_secret_key');
    Http::fake(function () {
        return Http::response(
            json_decode(
                '{"message": "Hosted Link", "status": "success", "data": {"checkout_url": "https://checkout.chapa.co/checkout/payment/V38JyhpTygC9QimkJrdful9oEjih0heIv53eJ1MsJS6xG"}}',
                true
            ),
            200
        );
    });

    /** @var AcceptPaymentResponse $response */
    $response = Chapa::acceptPayment(
        Money::ETB(100),
        new User(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            phoneNumber: '1234567890'
        ),
        'https://example.com/return'
    );

    expect($response->status)->toBe('success');
    expect($response->checkout_url)->toBeString();
    expect($response->transaction_id)->toBeString();
});

it('accepts payments with customization', function (): void {
    config()->set('chapa.secret_key', 'test_secret_key');
    Http::fake(function () {
        return Http::response(
            json_decode(
                '{"message": "Hosted Link", "status": "success", "data": {"checkout_url": "https://checkout.chapa.co/checkout/payment/V38JyhpTygC9QimkJrdful9oEjih0heIv53eJ1MsJS6xG"}}',
                true
            ),
            200
        );
    });

    /** @var AcceptPaymentResponse $response */
    $response = Chapa::acceptPayment(
        Money::ETB(100),
        new User(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            phoneNumber: '1234567890'
        ),
        'https://example.com/return'
    );

    expect($response->status)->toBe('success');
    expect($response->checkout_url)->toBeString();
    expect($response->transaction_id)->toBeString();
});

it('throws invalid argument exception if secret key is not set', function (): void {
    config()->set('chapa.secret_key', null);

    expect(fn () => Chapa::acceptPayment(
        Money::ETB(100),
        new User(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            phoneNumber: '1234567890'
        ),
        'https://example.com/return'
    ))->toThrow(InvalidArgumentException::class);
});

it('verifies payments', function (): void {
    config()->set('chapa.secret_key', 'test_secret_key');
    $tx_ref = 'vp_chapa_'.str()->random(10);
    Http::fake(function () use ($tx_ref) {
        return Http::response(
            json_decode(
                '{
                    "message": "Payment details",
                    "status": "success",
                    "data": {
                        "first_name": "John",
                        "last_name": "Doe",
                        "email": "john.doe@example.com",
                        "currency": "ETB",
                        "amount": 100,
                        "charge": 3.5,
                        "mode": "test",
                        "method": "test",
                        "type": "API",
                        "status": "success",
                        "reference": "6jnheVKQEmy",
                        "tx_ref": "'.$tx_ref.'",
                        "customization": {
                            "title": "Payment for my favourite merchant",
                            "description": "I love online payments",
                            "logo": null
                        },
                        "meta": null,
                        "created_at": "2023-02-02T07:05:23.000000Z",
                        "updated_at": "2023-02-02T07:05:23.000000Z"
                        }
                }',
                true
            ),
            200
        );
    });

    /** @var VerifyPaymentResponse $response */
    $response = Chapa::verifyPayment($tx_ref);

    expect($response->status)->toBe('success');
    expect($response->data['tx_ref'])->toBe($tx_ref);
});

it('can refund transactions', function (): void {
    config()->set('chapa.secret_key', 'test_secret_key');
    $tx_ref = 'vp_chapa_'.str()->random(10);
    Http::fake(function () {
        return Http::response(
            json_decode(
                '{
                    "message": "Refund initiated successfully. Processing time: 1-3 business days",
                    "status": "success",
                    "data": {
                        "id": 730,
                        "chapa_reference": "APezQ1KKswbb",
                        "bank_reference": "BLC9JI3G21",
                        "amount": "100.00",
                        "ref": "MERC-DIS-REF-s223VGvQFJk",
                        "currency": "ETB",
                        "status": "Refund Initiated",
                        "reason": null,
                        "merchant_reference": "OTAS379IOSHJ",
                        "created_at": "2024-12-13T17:33:27.000000Z",
                        "updated_at": "2024-12-13T17:33:27.000000Z"
                    }
                }',
                true
            ),
            200
        );
    });

    /** @var RefundResponse $response */
    $response = Chapa::refund($tx_ref, Money::ETB(10000), 'Customer requested refund');

    expect($response->status)->toBe('success');
    expect($response->data['chapa_reference'])->toBeString();
    expect($response->data['amount'])->toBe('100.00');
});

it('initializes a hosted payment using api v2', function (): void {
    config()->set('chapa.secret_key', 'test_secret_key');
    config()->set('chapa.api_version', 'v2');
    Http::fake([
        '*' => Http::response([
            'status' => 'success',
            'message' => 'Payment initialized successfully',
            'data' => ['checkout_url' => 'https://checkout.chapa.global/payment/CHAPA123'],
        ]),
    ]);

    $response = Chapa::acceptPayment(
        Money::ETB(10000),
        new User('John', 'Doe', 'john.doe@example.com', '+251911111111'),
        'https://example.com/return'
    );

    expect($response->status)->toBe('success')
        ->and($response->transaction_id)->toStartWith('vp_chapa_');

    Http::assertSent(function (Request $request): bool {
        $data = $request->data();

        return $request->url() === 'https://api.chapa.global/v2/payments/hosted'
            && $request->hasHeader('Authorization', 'Bearer test_secret_key')
            && $data['amount'] === '100.00'
            && $data['currency'] === 'ETB'
            && str_starts_with($data['merchant_reference'], 'vp_chapa_')
            && $data['customer'] === [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
                'phone_number' => '+251911111111',
            ]
            && ! array_key_exists('return_url', $data)
            && ! array_key_exists('callback_url', $data)
            && ! array_key_exists('customization', $data);
    });
});

it('maps api v2 hosted payment errors to the existing response', function (): void {
    config()->set('chapa.secret_key', 'test_secret_key');
    config()->set('chapa.api_version', 'v2');
    Http::fake([
        '*' => Http::response([
            'status' => 'error',
            'message' => 'Invalid request',
            'error' => [
                'code' => 'INVALID_VALUE',
                'details' => ['amount' => ['The amount must be positive.']],
            ],
        ], 422),
    ]);

    $response = Chapa::acceptPayment(
        Money::ETB(10000),
        new User('John', 'Doe', 'john.doe@example.com', '+251911111111'),
        'https://example.com/return'
    );

    expect($response->status)->toBe('error')
        ->and($response->message)->toBe('Invalid request')
        ->and($response->checkout_url)->toBeNull()
        ->and($response->validation_errors)->toBe([
            'amount' => ['The amount must be positive.'],
        ]);
});

it('verifies a payment using api v2', function (): void {
    config()->set('chapa.secret_key', 'test_secret_key');
    config()->set('chapa.api_version', 'v2');
    Http::fake([
        '*' => Http::response([
            'status' => 'success',
            'message' => 'Payment retrieved successful',
            'data' => ['chapa_reference' => 'CHAPA123', 'status' => 'success'],
        ]),
    ]);

    $response = Chapa::verifyPayment('CHAPA123');

    expect($response->data['chapa_reference'])->toBe('CHAPA123');
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.chapa.global/v2/payments/CHAPA123/verify');
});

it('creates full and partial refunds using api v2', function (?Money $amount, array $expected): void {
    config()->set('chapa.secret_key', 'test_secret_key');
    config()->set('chapa.api_version', 'v2');
    Http::fake([
        '*' => Http::response([
            'status' => 'success',
            'message' => 'Refund processed successfully',
            'data' => ['chapa_reference' => 'RFND123'],
        ]),
    ]);

    Chapa::refund('CHAPA123', $amount, $amount ? 'Customer request' : null);

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.chapa.global/v2/refunds'
        && $request->data() === $expected);
})->with([
    'full refund' => [null, ['payment_reference' => 'CHAPA123']],
    'partial refund' => [Money::ETB(10000), [
        'payment_reference' => 'CHAPA123',
        'amount' => '100.00',
        'reason' => 'Customer request',
    ]],
]);

it('rejects unsupported api versions', function (): void {
    config()->set('chapa.secret_key', 'test_secret_key');
    config()->set('chapa.api_version', 'v3');

    expect(fn () => app('chapa'))->toThrow(InvalidArgumentException::class, 'Unsupported Chapa API version');
});
