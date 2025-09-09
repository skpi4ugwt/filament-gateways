<?php

namespace App\Filament\Resources\PaymentGatewaySettings\Pages;

use App\Filament\Resources\PaymentGatewaySettingResource;
use Filament\Resources\Pages\EditRecord;
use Labify\Gateways\Models\PaymentGatewaySetting;

class EditPaymentGatewaySetting extends EditRecord
{
    protected static string $resource = PaymentGatewaySettingResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['is_default'])) {
            PaymentGatewaySetting::where('id', '!=', $this->record->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        return $data;
    }
}
