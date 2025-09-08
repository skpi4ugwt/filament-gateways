<?php
// config/payments.php
return [
    'default' => env('PAYMENTS_DEFAULT', 'razorpay'),
    'webhook_paths' => [
        'razorpay' => '/api/payments/razorpay/webhook',
        'payu'     => '/api/payments/payu/webhook',
        'easebuzz' => '/api/payments/easebuzz/webhook',
    ],
];