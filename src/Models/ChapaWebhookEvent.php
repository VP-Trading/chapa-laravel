<?php

declare(strict_types=1);

namespace Vptrading\ChapaLaravel\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ChapaWebhookEvent extends Model
{
    use HasUuids;

    protected $table = 'chapa_webhook_events';
}
