<?php

declare(strict_types=1);

namespace Vptrading\ChapaLaravel\Facades;

use Illuminate\Support\Facades\Facade;

class Chapa extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'chapa';
    }
}
