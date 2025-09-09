<?php

namespace App\Filament\Resources\PaymentGatewaySettings\Pages;

use App\Filament\Resources\PaymentGatewaySettingResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListPaymentGatewaySettings extends ListRecords
{
    protected static string $resource = PaymentGatewaySettingResource::class;

     protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
