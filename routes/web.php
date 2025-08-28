<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Vptrading\ChapaLaravel\Http\Controllers\WebhookController;

Route::post(config('chapa.callback_url'), WebhookController::class)->name('chapa.webhook');
