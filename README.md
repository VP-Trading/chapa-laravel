# Chapa Laravel Package

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

The Customization in the acceptPayment() method is **optional** and can be left out.

// Redirect user to payment page
return redirect($response->checkout_url);
```

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

> **Note:** This package is still under development.

**_🚀 And that's it. Do your thing and Give us a star if this helped you.🚀_**
