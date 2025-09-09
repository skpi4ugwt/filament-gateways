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
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Tables;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class PaymentGatewaySettingResource extends Resource
{
    protected static ?string $model = PaymentGatewaySetting::class;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';
    protected static string | UnitEnum | null $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Payment Gateways';
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
