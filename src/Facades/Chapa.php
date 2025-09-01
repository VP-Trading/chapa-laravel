<?php

declare(strict_types=1);

namespace Vptrading\ChapaLaravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array acceptPayment(\Money\Money $amount, \Vptrading\ChapaLaravel\ValueObjects\User $user, string $returnUrl)
 * @method static array verifyPayment(string $transactionId)
 */
class Chapa extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'chapa';
    }
}
