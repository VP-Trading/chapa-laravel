<?php

declare(strict_types=1);

namespace Vptrading\ChapaLaravel\Http\Controllers;

use Illuminate\Http\Request;
use Vptrading\ChapaLaravel\Models\ChapaWebhookEvent;

class WebhookController
{
    public function __invoke(Request $request)
    {
        if (! is_null(config('chapa.webhook_secret'))) {
            $secret = config('chapa.webhook_secret');

            $hash = hash_hmac('sha256', $request->getContent(), $secret);

            $signature = $request->header('x-chapa-signature');

            if (! is_string($signature) || ! hash_equals($hash, $signature)) {
                return response()->json(['message' => 'Invalid signature'], 403);
            }
        }

        $content = json_decode($request->getContent(), true);
        $merchantReference = $content['merchant_reference'] ?? $content['tx_ref'] ?? null;
        $chapaReference = $content['chapa_reference'] ?? $content['reference'] ?? null;

        $duplicateQuery = ChapaWebhookEvent::where('event_type', $content['event']);
        $merchantReference !== null
            ? $duplicateQuery->where('tx_ref', $merchantReference)
            : $duplicateQuery->where('chapa_ref', $chapaReference);

        if ($duplicateQuery->exists()) {
            return response()->json(['message' => 'Duplicate webhook'], 200);
        }

        ChapaWebhookEvent::create([
            'event_type' => $content['event'],
            'tx_ref' => $merchantReference,
            'chapa_ref' => $chapaReference,
            'status' => $content['status'],
            'amount' => $content['amount'],
            'charge' => $content['service_fee'] ?? $content['charge'] ?? 0,
            'currency' => $content['currency'],
            'type' => $content['payment_type'] ?? $content['webhook_type'] ?? $content['type'] ?? 'unknown',
            'data' => json_encode($content),
        ]);

        return response()->json(['message' => 'Webhook received'], 200);
    }
}
