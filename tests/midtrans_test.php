<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(\App\Services\MidtransService::class);
$service->configure();

echo "Server Key: " . $service->getServerKey() . PHP_EOL;
echo "Client Key: " . $service->getClientKey() . PHP_EOL;
echo "Is Production: " . ($service->isProduction() ? 'true' : 'false') . PHP_EOL;

try {
    $token = \Midtrans\Snap::getSnapToken([
        'transaction_details' => [
            'order_id' => 'TEST-' . time(),
            'gross_amount' => 25000,
        ],
        'customer_details' => [
            'first_name' => 'Donatur Test',
            'email' => 'donatur@test.com',
        ],
    ]);
    echo "SUCCESS! Snap Token: " . $token . PHP_EOL;
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
