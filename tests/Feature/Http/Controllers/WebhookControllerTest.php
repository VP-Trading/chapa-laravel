<?php

declare(strict_types=1);

it('can receive a webhook event', function (): void {
    config()->set('chapa.webhook_secret', 'test_secret');
    $payload = [
        'event' => 'charge.success',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'johndoe@example.com',
        'mobile' => '25190000000',
        'currency' => 'ETB',
        'amount' => '400.00',
        'charge' => '12.00',
        'status' => 'success',
        'mode' => 'live',
        'reference' => 'AP634JFwEbxd',
        'created_at' => '2023-08-27T19:21:18.000000Z',
        'updated_at' => '2023-08-27T19:21:27.000000Z',
        'type' => 'API',
        'tx_ref' => '4FGFF4FFGD3',
        'payment_method' => 'telebirr',
        'customization' => [
            'title' => null,
            'description' => null,
            'logo' => null,
        ],
        'meta' => null,
    ];
    $hash = hash_hmac('sha256', json_encode($payload), 'test_secret');
    $response = $this->postJson(
        route('chapa.webhook'),
        $payload,
        ['x-chapa-signature' => $hash],
    );

    $response->assertStatus(200);
    $this->assertDatabaseHas('chapa_webhook_events', [
        'event_type' => 'charge.success',
        'tx_ref' => '4FGFF4FFGD3',
        'status' => 'success',
        'amount' => '400.00',
        'currency' => 'ETB',
    ]);
});

it('rejects a webhook event with invalid signature', function (): void {
    config()->set('chapa.webhook_secret', 'test_secret');
    $payload = [
        'event' => 'charge.success',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'johndoe@example.com',
        'mobile' => '25190000000',
        'currency' => 'ETB',
        'amount' => '400.00',
        'charge' => '12.00',
        'status' => 'success',
        'mode' => 'live',
        'reference' => 'AP634JFwEbxd',
        'created_at' => '2023-08-27T19:21:18.000000Z',
        'updated_at' => '2023-08-27T19:21:27.000000Z',
        'type' => 'API',
        'tx_ref' => '4FGFF4FFGD3',
        'payment_method' => 'telebirr',
        'customization' => [
            'title' => null,
            'description' => null,
            'logo' => null,
        ],
        'meta' => null,
    ];
    $hash = hash_hmac('sha256', json_encode($payload), 'test_wrong_secrets');
    $response = $this->postJson(
        route('chapa.webhook'),
        $payload,
        ['x-chapa-signature' => $hash],
    );

    $response->assertStatus(403);
});

it('can receive webhook with no secret', function (): void {
    config()->set('chapa.webhook_secret', null);
    $payload = [
        'event' => 'charge.success',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'johndoe@example.com',
        'mobile' => '25190000000',
        'currency' => 'ETB',
        'amount' => '400.00',
        'charge' => '12.00',
        'status' => 'success',
        'mode' => 'live',
        'reference' => 'AP634JFwEbxd',
        'created_at' => '2023-08-27T19:21:18.000000Z',
        'updated_at' => '2023-08-27T19:21:27.000000Z',
        'type' => 'API',
        'tx_ref' => '4FGFF4FFGD3',
        'payment_method' => 'telebirr',
        'customization' => [
            'title' => null,
            'description' => null,
            'logo' => null,
        ],
        'meta' => null,
    ];
    $response = $this->postJson(
        route('chapa.webhook'),
        $payload
    );

    $response->assertStatus(200);
    $this->assertDatabaseHas('chapa_webhook_events', [
        'event_type' => 'charge.success',
        'tx_ref' => '4FGFF4FFGD3',
        'status' => 'success',
        'amount' => '400.00',
        'currency' => 'ETB',
    ]);
});

it('responds duplicate if tx_ref and event type are identical', function (): void {
    config()->set('chapa.webhook_secret', 'test_secret');
    $payload = [
        'event' => 'charge.success',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'johndoe@example.com',
        'mobile' => '25190000000',
        'currency' => 'ETB',
        'amount' => '400.00',
        'charge' => '12.00',
        'status' => 'success',
        'mode' => 'live',
        'reference' => 'AP634JFwEbxd',
        'created_at' => '2023-08-27T19:21:18.000000Z',
        'updated_at' => '2023-08-27T19:21:27.000000Z',
        'type' => 'API',
        'tx_ref' => '4FGFF4FFGD3',
        'payment_method' => 'telebirr',
        'customization' => [
            'title' => null,
            'description' => null,
            'logo' => null,
        ],
        'meta' => null,
    ];
    $hash = hash_hmac('sha256', json_encode($payload), 'test_secret');
    $response = $this->postJson(
        route('chapa.webhook'),
        $payload,
        ['x-chapa-signature' => $hash],
    );

    $response->assertStatus(200);
    $this->assertDatabaseHas('chapa_webhook_events', [
        'event_type' => 'charge.success',
        'tx_ref' => '4FGFF4FFGD3',
        'status' => 'success',
        'amount' => '400.00',
        'currency' => 'ETB',
    ]);

    $response = $this->postJson(
        route('chapa.webhook'),
        $payload,
        ['x-chapa-signature' => $hash],
    );
    $response->assertStatus(200)
        ->assertJson(['message' => 'Duplicate webhook']);
});

it('normalizes api v2 webhook fields', function (): void {
    config()->set('chapa.webhook_secret', 'test_secret');
    $payload = [
        'webhook_type' => 'payment',
        'event' => 'payment.success',
        'status' => 'success',
        'mode' => 'test',
        'currency' => 'ETB',
        'amount' => '400.00',
        'merchant_reference' => 'ORDER-123',
        'chapa_reference' => 'CHAPA-123',
        'payment_type' => 'Hosted',
        'payment_method' => 'telebirr',
        'service_fee' => '12.00',
        'created_at' => '2025-11-07T12:00:00Z',
        'updated_at' => '2025-11-07T12:01:00Z',
        'meta' => ['order_id' => 123],
    ];
    $hash = hash_hmac('sha256', json_encode($payload), 'test_secret');

    $response = $this->postJson(route('chapa.webhook'), $payload, [
        'x-chapa-signature' => $hash,
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('chapa_webhook_events', [
        'event_type' => 'payment.success',
        'tx_ref' => 'ORDER-123',
        'chapa_ref' => 'CHAPA-123',
        'status' => 'success',
        'amount' => '400.00',
        'charge' => '12.00',
        'currency' => 'ETB',
        'type' => 'Hosted',
    ]);
});

it('accepts api v2 webhooks with missing optional payment fields', function (): void {
    config()->set('chapa.webhook_secret', null);
    $payload = [
        'webhook_type' => 'refund',
        'event' => 'payment.fully_refunded',
        'status' => 'completed',
        'mode' => 'test',
        'currency' => 'ETB',
        'amount' => '400.00',
        'merchant_reference' => 'ORDER-123',
        'chapa_reference' => 'RFND-123',
        'created_at' => '2025-11-07T12:00:00Z',
        'updated_at' => '2025-11-07T12:01:00Z',
        'meta' => [],
    ];

    $this->postJson(route('chapa.webhook'), $payload)->assertOk();

    $this->assertDatabaseHas('chapa_webhook_events', [
        'event_type' => 'payment.fully_refunded',
        'tx_ref' => 'ORDER-123',
        'chapa_ref' => 'RFND-123',
        'charge' => '0.00',
        'type' => 'refund',
    ]);
});

it('deduplicates api v2 webhooks by event and merchant reference', function (): void {
    config()->set('chapa.webhook_secret', null);
    $payload = [
        'webhook_type' => 'payment',
        'event' => 'payment.success',
        'status' => 'success',
        'currency' => 'ETB',
        'amount' => '400.00',
        'merchant_reference' => 'ORDER-123',
        'chapa_reference' => 'CHAPA-123',
    ];

    $this->postJson(route('chapa.webhook'), $payload)->assertOk();
    $this->postJson(route('chapa.webhook'), array_merge($payload, [
        'chapa_reference' => 'CHAPA-CHANGED',
    ]))->assertOk()->assertJson(['message' => 'Duplicate webhook']);
});

it('rejects a signed webhook with a missing signature', function (): void {
    config()->set('chapa.webhook_secret', 'test_secret');

    $this->postJson(route('chapa.webhook'), [])->assertForbidden();
});
