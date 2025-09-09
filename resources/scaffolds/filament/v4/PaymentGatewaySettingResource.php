<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentGatewaySettings\Pages\CreatePaymentGatewaySetting;
use App\Filament\Resources\PaymentGatewaySettings\Pages\EditPaymentGatewaySetting;
use App\Filament\Resources\PaymentGatewaySettings\Pages\ListPaymentGatewaySettings;
use App\Filament\Resources\PaymentGatewaySettings\Schemas\PaymentGatewaySettingForm;
use App\Filament\Resources\PaymentGatewaySettings\Tables\PaymentGatewaySettingsTable;
use App\Models\PaymentGatewaySetting;
use BackedEnum;
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PaymentGatewaySettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentGatewaySettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentGatewaySettings::route('/'),
            'create' => CreatePaymentGatewaySetting::route('/create'),
            'edit' => EditPaymentGatewaySetting::route('/{record}/edit'),
        ];
    }
}
