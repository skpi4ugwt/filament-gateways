<?php

// src/Payments/PaymentSettingsRepository.php
namespace Labify\Gateways\Payments;

use Illuminate\Support\Facades\Cache;
use Labify\Gateways\Models\PaymentGatewaySetting;

class PaymentSettingsRepository
{
    public function all(): array
    {
        return Cache::remember('payment.gateways.settings', 60, function () {
            return PaymentGatewaySetting::query()->get()->mapWithKeys(function ($row) {
                return [$row->name => [
                    'class' => $this->classFor($row->name),
                    'is_active' => (bool)$row->is_active,
                    'is_default' => (bool)$row->is_default,
                    'base_url' => $row->base_url,
                    'currency' => $row->currency,
                    'methods' => $row->methods ?? [],
                    'meta' => $row->meta ?? [],
                    'key' => $row->api_key,
                    'secret' => $row->api_secret,
                    'webhook_secret' => $row->webhook_secret,
                ]];
            })->toArray();
        });
    }

    public function defaultName(): ?string
    {
        foreach ($this->all() as $name => $cfg) if (!empty($cfg['is_default'])) return $name;
        return array_key_first($this->all()) ?: null;
    }

    public function refreshCache(): void
    {
        Cache::forget('payment.gateways.settings');
        $this->all();
    }

    protected function classFor(string $name): ?string
    {
        return match ($name) {
            'razorpay' => \Labify\Gateways\Payments\Gateways\RazorpayGateway::class,
            'payu'     => \Labify\Gateways\Payments\Gateways\PayUGateway::class,
            'easebuzz' => \Labify\Gateways\Payments\Gateways\EasebuzzGateway::class,
            default    => null,
        };
    }
}
