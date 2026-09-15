<?php

declare(strict_types=1);

return [
    'secret_key' => env('CHAPA_SECRET_KEY'),
    'api_version' => env('CHAPA_API_VERSION', 'v1'),
    'base_url' => env('CHAPA_BASE_URL', 'https://api.chapa.co/v1'),
    'v2_base_url' => env('CHAPA_V2_BASE_URL', 'https://api.chapa.global/v2'),
    'callback_url' => env('CHAPA_CALLBACK_URL', '/vp/chapa/webhook'),
    'webhook_secret' => env('CHAPA_WEBHOOK_SECRET'),
    'ref_prefix' => env('CHAPA_REF_PREFIX', 'vp_chapa_'),
];
