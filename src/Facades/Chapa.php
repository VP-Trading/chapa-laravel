<?php

declare(strict_types=1);

namespace Vptrading\ChapaLaravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Vptrading\ChapaLaravel\Dtos\AcceptPaymentResponse acceptPayment(\Money\Money $amount, \Vptrading\ChapaLaravel\ValueObjects\User $user, string $returnUrl, \Vptrading\ChapaLaravel\ValueObjects\Customization|null $customization = null)
 * @method static \Vptrading\ChapaLaravel\Dtos\VerifyPaymentResponse verifyPayment(string $transactionId)
 * @method static \Vptrading\ChapaLaravel\Dtos\RefundResponse refund(string $transactionId, \Money\Money|null $amount = null, string|null $reason = null)
 */
class Chapa extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'chapa';
    }
}
