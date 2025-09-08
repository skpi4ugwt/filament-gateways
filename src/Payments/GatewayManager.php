<?php

// src/Payments/GatewayManager.php
namespace Labify\Gateways\Payments;

use InvalidArgumentException;
use Labify\Gateways\Payments\Contracts\PaymentGateway;

class GatewayManager
{
    public function __construct(private PaymentSettingsRepository $repo) {}

    public function use(?string $name = null): PaymentGateway
    {
        $all = $this->repo->all();
        $name ??= $this->repo->defaultName();

        $gw = $all[$name] ?? null;
        if (!$gw || empty($gw['class']) || empty($gw['is_active'])) {
            throw new InvalidArgumentException("Unknown or inactive gateway [$name]");
        }

        return app($gw['class'], ['cfg' => $gw]);
    }
}
