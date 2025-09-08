<?php

// src/Payments/Contracts/PaymentGateway.php
namespace Labify\Gateways\Payments\Contracts;

use Illuminate\Http\Request;
use Labify\Gateways\Models\Payment;

interface PaymentGateway
{
    public function createIntent(Payment $payment, array $options = []): array;
    public function capture(Payment $payment, array $options = []): array;
    public function refund(Payment $payment, int $amount, array $options = []): array;
    public function verifyAndNormalizeWebhook(Request $request): array;
    public function handleWebhook(array $event): void;
}
