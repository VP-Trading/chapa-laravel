<?php

declare(strict_types=1);

it('can receive a webhook event', function (): void {

    $response = $this->postJson(
        config('chapa.webhook_endpoint'),
        json_decode('{
                  "event": "charge.success",
                  "first_name": "John",
                    "last_name": "Doe",
                    "email": "johndoe@example.com",
                    "mobile": "25190000000",
                    "currency": "ETB",
                    "amount": "400.00",
                    "charge": "12.00",
                    "status": "success",
                    "mode": "live",
                    "reference": "AP634JFwEbxd",
                    "created_at": "2023-08-27T19:21:18.000000Z",
                    "updated_at": "2023-08-27T19:21:27.000000Z",
                    "type": "API",
                    "tx_ref": "4FGFF4FFGD3",
                    "payment_method": "telebirr",
                    "customization": {
                    "title": null,
                    "description": null,
                    "logo": null
                    },
                    "meta": null
                    }', true)
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
