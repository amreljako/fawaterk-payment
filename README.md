# Fawaterk Payment Integration for Laravel

A Laravel package to easily integrate with the [Fawaterk Payment API](https://fawaterk.com).  
It allows you to create invoices, fetch invoice status, and securely handle payment webhooks.

---

## Installation

> Make sure your Laravel version is 9.x, 10.x, or 11.x and PHP is 8.0+

### Step 1: Require the package via Composer

If you've tagged a release (e.g. `v1.0.0`):

```bash
composer require amreljako/fawaterk-payment
```

Or for development:

```bash
composer require amreljako/fawaterk-payment:dev-main
```

---

## Configuration

### Step 2: Add your API token to `.env`

```env
FAWATERK_TOKEN=your_fawaterk_token_here
```

### Step 3: (Optional) Publish the config file

```bash
php artisan vendor:publish --tag=config --provider="AmrEljako\FawaterkPayment\FawaterkServiceProvider"
```

This will publish `config/fawaterk.php`.

---

## Usage

### Step 4: Create an Invoice

```php
use AmrEljako\FawaterkPayment\Fawaterk;

$fawaterk = app(Fawaterk::class);

$response = $fawaterk->createInvoice([
    'payment_method_id' => 2, // 2=Card, 3=Fawry, 4=Wallet
    'cartTotal' => '1000',
    'currency' => 'EGP',
    'invoice_number' => 'INV-' . time(),
    'payLoad' => 'any_custom_payload_like_uuid',
    'customer' => [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'phone' => '01000000000',
        'address' => 'Cairo, Egypt'
    ],
    'redirectionUrls' => [
        'successUrl' => route('fawaterk.success'),
        'failUrl' => route('fawaterk.fail'),
        'pendingUrl' => route('fawaterk.pending')
    ],
    'cartItems' => [
        [
            'name' => 'Product Name',
            'price' => '1000',
            'quantity' => '1'
        ]
    ]
]);

return redirect()->away($response->data->payment_data->redirectTo);
```

> 🟠 If you're using **wallet payment** (`payment_method_id = 4`), the phone number must be a valid Egyptian number starting with `01`, e.g., `01012345678`.

---

### Step 5: Get Invoice Data

```php
$invoice = $fawaterk->getInvoice($invoice_id);
```

---

### Step 6: Webhook Handling

Create a webhook route in `routes/web.php`:

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use AmrEljako\FawaterkPayment\Fawaterk;

Route::post('/fawaterk/webhook', function (Request $request) {
    $fawaterk = app(Fawaterk::class);

    if (! $fawaterk->verifyWebhook($request->all())) {
        Log::warning('Invalid Fawaterk Webhook!');
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Process verified data
    Log::info(' Webhook verified successfully.', $request->all());

    // You can update your order/payment status here

    return response()->json(['message' => 'OK']);
});
```

---

## Webhook Signature Verification

Fawaterk sends a secure HMAC hash called `hashKey`.  
The package automatically verifies the signature using your `FAWATERK_TOKEN`.

---

## Supported Payment Methods

| ID | Method   |
|----|----------|
| 2  | Card     |
| 3  | Fawry    |
| 4  | Wallets  | ✅ Requires valid `01` phone number

---

## Folder Structure

```
fawaterk-payment/
├── src/
│   ├── Fawaterk.php
│   └── FawaterkServiceProvider.php
├── config/
│   └── fawaterk.php
└── composer.json
```

---

## 📎 Example Controller

```php
use AmrEljako\FawaterkPayment\Fawaterk;

class PaymentController extends Controller
{
    public function pay()
    {
        $fawaterk = app(Fawaterk::class);

        $invoice = $fawaterk->createInvoice([
            'payment_method_id' => 2,
            'cartTotal' => '1000',
            'currency' => 'EGP',
            'invoice_number' => 'INV-' . time(),
            'payLoad' => 'uuid-1234',
            'customer' => [
                'first_name' => 'Ali',
                'last_name' => 'Saleh',
                'email' => 'ali@example.com',
                'phone' => '01000000000',
                'address' => 'Alexandria'
            ],
            'redirectionUrls' => [
                'successUrl' => route('fawaterk.success'),
                'failUrl' => route('fawaterk.fail'),
                'pendingUrl' => route('fawaterk.pending')
            ],
            'cartItems' => [
                ['name' => 'Ticket', 'price' => '1000', 'quantity' => '1']
            ]
        ]);

        return redirect()->away($invoice->data->payment_data->redirectTo);
    }
}
```

---

## Required Routes Example

```php
Route::view('/payment/success', 'payment.success')->name('fawaterk.success');
Route::view('/payment/fail', 'payment.fail')->name('fawaterk.fail');
Route::view('/payment/pending', 'payment.pending')->name('fawaterk.pending');
```

---

## Notes

- Always store `invoice_id`, `invoice_key`, and `status` for reference.
- `payLoad` can be used to track your order or user.
- Make sure your `.env` has the correct token from your Fawaterk dashboard.

---

## License

MIT © [Amr Elsayed](mailto:amreljako@gmail.com)
