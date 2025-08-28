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
    CHAPA_WEBHOOK_ENDPOINT=vp/chapa/webhook
    CHAPA_REF_PREFIX=vp_chapa_
    ```

5. **Ready to use!**
   You can now use the package in your Laravel application.

## Usage

Here are some basic usage examples:

### Initialize Payment

```php
use Vptrading\ChapaLaravel\Facades\Chapa;

$response = Chapa::acceptPayment([
    Money::ETB(100),
    new UserValueObject(
        firstName: 'John',
        lastName: 'Doe',
        email: 'johndoe@example.com',
        phoneNumber: '0912345678'
    )
]);

// Redirect user to payment page
return redirect($response['data']['checkout_url']);
```

### Verify Payment

```php
use Vptrading\ChapaLaravel\Facades\Chapa;

$tx_ref = 'unique-tx-ref-123';
$verification = Chapa::verify($tx_ref);

if ($verification['status'] === 'success') {
    // Payment was successful
}
```

> **Note:** This package is still under development.

**_🚀 And that's it. Do your thing and Give us a star if this helped you.🚀_**
