<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentGatewaySettingResource\Pages;
use App\Filament\Resources\PaymentGatewaySettings\Schemas\PaymentGatewaySettingForm;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\{Action, EditAction};
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Labify\Gateways\Models\PaymentGatewaySetting;

class PaymentGatewaySettingResource extends Resource
{
    protected static ?string $model = PaymentGatewaySetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Payment Gateways';

    public static function form(Form $form): Form
    {
        return $form->schema(
            PaymentGatewaySettingForm::components()
        );
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_label')->label('Label')->searchable(),
                Tables\Columns\BadgeColumn::make('name')->label('Gateway')->colors([
                    'primary' => 'razorpay',
                    'warning' => 'payu',
                    'success' => 'easebuzz',
                ])->searchable(),
                Tables\Columns\TextColumn::make('currency')->badge(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\IconColumn::make('is_default')->boolean()->label('Default'),
                Tables\Columns\TextColumn::make('updated_at')->since(),
            ])
            ->recordActions([
                Action::make('test')
                    ->label('Test Connection')
                    ->icon('heroicon-o-wifi')
                    ->action(function (PaymentGatewaySetting $record) {
                        $name = $record->name;
                        $ok = false; $note = '';

                        $isReachable = function (?string $url): array {
                            if (empty($url)) return [false, 'Missing base URL'];
                            try {
                                $resp = Http::timeout(6)->withHeaders(['Accept'=>'application/json'])->get(rtrim($url,'/'));
                                $reachable = $resp->successful() || in_array($resp->status(), [301,302,400,401,403], true);
                                return [$reachable, 'HTTP '.$resp->status()];
                            } catch (\Throwable $e) {
                                return [false, $e->getMessage()];
                            }
                        };

                        try {
                            if ($name === 'razorpay') {
                                $base = rtrim($record->base_url ?: 'https://api.razorpay.com/v1', '/');
                                $res = Http::timeout(8)
                                    ->withBasicAuth((string)$record->api_key, (string)$record->api_secret)
                                    ->get($base.'/orders', ['count'=>1]);
                                $ok = $res->status() !== 401;
                                $note = 'Razorpay: GET /orders → HTTP '.$res->status();
                            } elseif ($name === 'payu') {
                                $env  = $record->meta['env'] ?? 'test';
                                $base = $record->base_url ?: ($env === 'live' ? 'https://secure.payu.in' : 'https://test.payu.in');
                                $has  = !empty($record->meta['merchant_key'] ?? null) && !empty($record->meta['salt'] ?? null);
                                [$reach,$why] = $isReachable($base);
                                $ok = $has && $reach;
                                $note = 'PayU: '.($has?'keys present; ':'keys missing; ').'base '.($reach?'reachable':'not reachable')." ($why)";
                            } elseif ($name === 'easebuzz') {
                                $env  = $record->meta['env'] ?? 'test';
                                $base = $record->base_url ?: ($env === 'live' ? 'https://api.easebuzz.in' : 'https://testpay.easebuzz.in');
                                $has  = !empty($record->meta['merchant_key'] ?? null) && !empty($record->meta['salt'] ?? null);
                                [$reach,$why] = $isReachable($base);
                                $ok = $has && $reach;
                                $note = 'Easebuzz: '.($has?'keys present; ':'keys missing; ').'base '.($reach?'reachable':'not reachable')." ($why)";
                            } else {
                                $ok = false; $note = 'Unsupported gateway (use Razorpay, PayU, or Easebuzz).';
                            }
                        } catch (\Throwable $e) {
                            $ok = false; $note = $e->getMessage();
                        }

                        ($ok ? Notification::make()->success() : Notification::make()->danger())
                            ->title($ok ? 'Connection OK' : 'Connection Failed')
                            ->body($note ?: 'Check credentials / base URL / environment')
                            ->send();
                    }),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPaymentGatewaySettings::route('/'),
            'create' => Pages\CreatePaymentGatewaySetting::route('/create'),
            'edit'   => Pages\EditPaymentGatewaySetting::route('/{record}/edit'),
        ];
    }
}
