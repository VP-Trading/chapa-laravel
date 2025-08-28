<?php

declare(strict_types=1);

return [
    'secret_key' => env('CHAPA_SECRET_KEY'),
    'public_key' => env('CHAPA_PUBLIC_KEY'),
    'encryption_key' => env('CHAPA_ENCRYPTION_KEY'),
    'base_url' => env('CHAPA_BASE_URL', 'https://api.chapa.co/v1'),
    'callback_url' => env('CHAPA_CALLBACK_URL'),
    'ref_prefix' => env('CHAPA_REF_PREFIX', 'vp_chapa_'),
    'webhook_endpoint' => env('CHAPA_WEBHOOK_ENDPOINT', 'vp/chapa/webhook'),
];
