<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Vptrading\ChapaLaravel\Http\Controllers\WebhookController;

Route::post(parse_url(config('chapa.callback_url'))['path'], WebhookController::class)->name('chapa.webhook');
