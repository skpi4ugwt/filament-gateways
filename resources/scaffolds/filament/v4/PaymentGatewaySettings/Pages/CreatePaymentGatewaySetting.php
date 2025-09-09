<?php

namespace App\Filament\Resources\PaymentGatewaySettings\Pages;

use App\Filament\Resources\PaymentGatewaySettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentGatewaySetting extends CreateRecord
{
    protected static string $resource = PaymentGatewaySettingResource::class;
}
