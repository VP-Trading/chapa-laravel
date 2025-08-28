<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Vptrading\ChapaLaravel\Http\Controllers\WebhookController;
use Vptrading\ChapaLaravel\Http\Middlewares\ForceJsonResponse;

Route::post(config('chapa.callback_url'), WebhookController::class)
    ->middleware(ForceJsonResponse::class)
    ->name('chapa.webhook');
