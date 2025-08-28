# Installation

To install the `chapa-laravel` package, follow these steps:

1. **Require the package via Composer:**

    ```bash
    composer require alemcinema/chapa-laravel
    ```

2. **Publish the configuration, route, and migration files (optional):**

    ```bash
    php artisan vendor:publish --provider="Vptrading\ChapaLaravel\ChapaServiceProvider"
    ```

    The package comes with config, route, and migration files to help you get started quickly.

3. **Configure your `.env` file:**
   Add your Chapa API key:

    ```bash
    CHAPA_API_KEY=your_chapa_api_key_here
    ```

4. **Ready to use!**
   You can now use the package in your Laravel application.

# Usage

Here are some basic usage examples:

## Initialize Payment

```php
use Vptrading\ChapaLaravel\Facades\Chapa;

$response = Chapa::acceptPayment([
    'amount' => 100,
    'currency' => 'ETB',
    'email' => 'customer@example.com',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'tx_ref' => 'unique-tx-ref-123',
    'callback_url' => route('chapa.callback'),
]);

// Redirect user to payment page
return redirect($response['data']['checkout_url']);
```

## Verify Payment

```php
use Vptrading\ChapaLaravel\Facades\Chapa;

$tx_ref = 'unique-tx-ref-123';
$verification = Chapa::verify($tx_ref);

if ($verification['status'] === 'success') {
    // Payment was successful
}
```
