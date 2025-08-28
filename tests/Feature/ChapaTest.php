<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Money\Money;
use Vptrading\ChapaLaravel\Facades\Chapa;
use Vptrading\ChapaLaravel\ValueObjects\UserValueObject;

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

    $response = Chapa::acceptPayment(
        Money::ETB(100),
        new UserValueObject(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            phoneNumber: '1234567890'
        ),
        'https://example.com/return'
    );

    expect($response['status'])->toBe('success');
    expect($response['data']['checkout_url'])->toBeString();
});

it('throws invalid argument exception if secret key is not set', function (): void {
    config()->set('chapa.secret_key', null);

    expect(fn () => Chapa::acceptPayment(
        Money::ETB(100),
        new UserValueObject(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            phoneNumber: '1234567890'
        ),
        'https://example.com/return'
    ))->toThrow(\InvalidArgumentException::class);
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

    $response = Chapa::verifyPayment($tx_ref);

    expect($response['status'])->toBe('success');
    expect($response['data']['tx_ref'])->toBe($tx_ref);
});
