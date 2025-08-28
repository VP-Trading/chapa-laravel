<?php

declare(strict_types=1);

namespace Vptrading\ChapaLaravel\Http\Controllers;

use Illuminate\Http\Request;
use Vptrading\ChapaLaravel\Models\ChapaWebhookEvent;

class WebhookController
{
    public function __invoke(Request $request)
    {
        if (! empty(config('chapa.secret_key'))) {
            $secret = config('chapa.webhook_secret');

            $hash = hash_hmac('sha256', $request->getContent(), $secret);

            if (! hash_equals($hash, $request->header('Chapa-Signature'))) {
                return response()->json(['message' => 'Invalid signature'], 403);
            }
        }

        $content = json_decode($request->getContent(), true);

        ChapaWebhookEvent::create([
            'event_type' => $content['event'],
            'tx_ref' => $content['tx_ref'] ?? null,
            'status' => $content['status'],
            'amount' => $content['amount'],
            'charge' => $content['charge'],
            'currency' => $content['currency'],
            'type' => $content['type'],
            'data' => json_encode($content),
        ]);

        return response()->json(['message' => 'Webhook received'], 200);
    }
}
