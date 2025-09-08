<?php

// src/Payments/PaymentService.php
namespace Labify\Gateways\Payments;

use Labify\Gateways\Models\Payment;

class PaymentService
{
    public function __construct(private GatewayManager $manager) {}

    public function start(?string $gateway, int $amount, string $currency, array $options = []): array
    {
        $payment = Payment::create([
            'gateway' => $gateway ?? $this->manager->use()->name ?? 'razorpay',
            'amount' => $amount,
            'currency' => strtolower($currency),
        ]);
        return $this->manager->use($payment->gateway)->createIntent($payment, $options);
    }

    public function capture(Payment $payment, array $options = []): array
    {
        return $this->manager->use($payment->gateway)->capture($payment, $options);
    }

    public function refund(Payment $payment, int $amount, array $options = []): array
    {
        return $this->manager->use($payment->gateway)->refund($payment, $amount, $options);
    }
}
