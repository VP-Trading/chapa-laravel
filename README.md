# Chapa Laravel Package

[![Total Downloads](https://poser.pugx.org/vp-trading/chapa-laravel/d/total.svg)](https://packagist.org/packages/vp-trading/chapa-laravel)
[![Latest Stable Version](https://poser.pugx.org/vp-trading/chapa-laravel/v/stable.svg)](https://packagist.org/packages/vp-trading/chapa-laravel)
[![License](https://poser.pugx.org/vp-trading/chapa-laravel/license.svg)](https://packagist.org/packages/vp-trading/chapa-laravel)


## Installation

To install the `chapa-laravel` package, follow these steps:

1. **Require the package via Composer:**

    ```bash
    composer require vp-trading/chapa-laravel
    ```

2. **Publish the configuration, route, and migration files:**

    ```bash
    php artisan vendor:publish --provider="Vptrading\ChapaLaravel\ChapaServiceProvider"
    ```

    The package comes with config, route, and migration files to help you get started quickly.

3. **Run the migration:**

    ```bash
    php artisan migrate
    ```

4. **Configure your `.env` file:**
   Add your Chapa API keys:

    ```bash
    CHAPA_SECRET_KEY=your_chapa_secret_key_here
    CHAPA_API_VERSION=v1
    CHAPA_CALLBACK_URL=/vp/chapa/webhook
    CHAPA_WEBHOOK_SECRET=54LZzhZbPs7yreuS6bAw74zS0KE7P4Mt1fiqRx7wOL8OdjUQHjBqsIpkpT2rm43S
    CHAPA_REF_PREFIX=vp_chapa_
    ```

5. **Ready to use!**
   You can now use the package in your Laravel application.

## Usage

> Before you start using this package you should know all amount must be put in **_cent values_**. The development of this package took standards from stripe. So for example if you want the amount be 100 Birr you will put 10000.

Here are some basic usage examples:

### Initialize Payment

```php
use Vptrading\ChapaLaravel\Facades\Chapa;
use VpTrading\ChapaLaravel\ValueObjects\User;
use VpTrading\ChapaLaravel\ValueObjects\Customization;
use Money\Money;

$response = Chapa::acceptPayment([
    Money::ETB(10000),
    new User(
        firstName: 'John',
        lastName: 'Doe',
        email: 'johndoe@example.com',
        phoneNumber: '0912345678'
    ),
    route('return-url'),
    new Customization(
        title: 'VP Solutions',
        description: 'This package is working like a charm.',
        logo: 'https://vptrading.et/wp-content/uploads/2023/08/cropped-VP-Logo-Symbol-White.png'
    )
]);

// Redirect user to payment page
return redirect($response->checkout_url);
```

The Customization in the acceptPayment() method is **optional** and can be left out.

## Chapa API v2

API v1 remains the default so existing applications continue to work without changes. To opt in to API v2, add:

```bash
CHAPA_API_VERSION=v2
```

The v2 API uses `https://api.chapa.global/v2` by default. You can override it with `CHAPA_V2_BASE_URL` when needed.

The existing `acceptPayment()`, `verifyPayment()`, and `refund()` PHP interfaces remain unchanged. Under v2:

- Hosted payments use the nested `customer` payload and a generated `merchant_reference`.
- `AcceptPaymentResponse::$transaction_id` continues to contain that generated merchant reference.
- `verifyPayment()` expects the Chapa payment reference returned by Chapa through verification or a webhook.
- `refund()` expects the successful payment's Chapa reference.
- The legacy `returnUrl` and `Customization` arguments are retained for source compatibility but are not sent by the v2 hosted-payment API. Configure redirect and checkout presentation using the options supported by your Chapa v2 dashboard.

During migration, the included webhook endpoint accepts both v1 and v2 payload formats. When `CHAPA_WEBHOOK_SECRET` is configured, both formats require a valid `x-chapa-signature` generated from the exact raw request body.

### Verify Payment

```php
use Vptrading\ChapaLaravel\Facades\Chapa;

$tx_ref = 'unique-tx-ref-123';
$verification = Chapa::verify($tx_ref);

if ($verification->data['status'] === 'success') {
    // Payment was successful
}
```

### Refund

```php
use Vptrading\ChapaLaravel\Facades\Chapa;
use Money\Money;

$chapaRef = "APfxLoHHsl1eD";
Chapa::refund($chapaRef, Money::ETB(10000), 'Client Requested Refund');
```

The amount and reason in the refund method are **optional** and can be left null.

### Webhook

Chapa sends webhooks for all payment-related events to your application. This package provides a default webhook controller to handle these events. You can extend or override the default controller `Vptrading\ChapaLaravel\Http\Controllers\WebhookController::class` to implement custom logic for different webhook events.

To customize webhook handling, create your own controller and update the route in your application as needed.

Default route for receiving webhook is:
`/vp/chapa/webhook`

You can change the route by changing the `CHAPA_CALLBACK_URL` env variable.

```php
class WebhookController extends ChapaWebhookController
{
    public function __invoke(Request $request)
    {
        parent::__invoke($request);

        // Handle your custom logic here.
    }
}
```

**_🚀 And that's it. Do your thing and Give us a star if this helped you.🚀_**
