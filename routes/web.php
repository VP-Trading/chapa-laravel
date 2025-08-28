<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Vptrading\ChapaLaravel\Http\Controllers\WebhookController;

Route::post(config('chapa.webhook_endpoint'), WebhookController::class);
