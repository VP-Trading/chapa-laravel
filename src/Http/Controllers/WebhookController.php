<?php

declare(strict_types=1);

namespace Vptrading\ChapaLaravel\Http\Controllers;

use Illuminate\Http\Request;
use Vptrading\ChapaLaravel\Models\ChapaWebhookEvent;

class WebhookController
{
    public function __invoke(Request $request)
    {
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
