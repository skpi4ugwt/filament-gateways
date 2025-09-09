<?php

namespace App\Filament\Resources\PaymentGatewaySettings;

use App\Filament\Resources\PaymentGatewaySettings\Pages\CreatePaymentGatewaySetting;
use App\Filament\Resources\PaymentGatewaySettings\Pages\EditPaymentGatewaySetting;
use App\Filament\Resources\PaymentGatewaySettings\Pages\ListPaymentGatewaySettings;
use App\Filament\Resources\PaymentGatewaySettings\Schemas\PaymentGatewaySettingForm;
use App\Filament\Resources\PaymentGatewaySettings\Tables\PaymentGatewaySettingsTable;
use App\Models\PaymentGatewaySetting;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Forms\Form;      // ⬅️ use Form, not Schema
use Filament\Tables\Table;

class PaymentGatewaySettingResource extends Resource
{
    protected static ?string $model = PaymentGatewaySetting::class;

    // Remove nav properties to avoid type mismatches; use getters instead
    public static function getNavigationIcon(): BackedEnum|string|null
    {
        // You can return an enum if you prefer, but a string is simplest:
        return 'heroicon-o-credit-card';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Settings';
    }

    public static function getNavigationLabel(): ?string
    {
        return 'Payment Gateways';
    }

    public static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        // Delegate to your schema helper that returns an array of components
        return $form->schema(
            PaymentGatewaySettingForm::components()
        );
    }

    public static function table(Table $table): Table
    {
        // If you have a separate table helper, keep using it:
        return PaymentGatewaySettingsTable::configure($table);

        // Or inline columns/actions here if you prefer.
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPaymentGatewaySettings::route('/'),
            'create' => CreatePaymentGatewaySetting::route('/create'),
            'edit'   => EditPaymentGatewaySetting::route('/{record}/edit'),
        ];
    }
}
