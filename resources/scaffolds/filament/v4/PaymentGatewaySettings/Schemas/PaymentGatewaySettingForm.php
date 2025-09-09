<?php

namespace App\Filament\Resources\PaymentGatewaySettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Rule;
use Filament\Schemas\Schema;
use	Filament\Schemas\Components\Utilities\Get;

class PaymentGatewaySettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Gateway')
                ->schema([
                    Select::make('name')
                        ->label('Gateway')
                        ->options([
                           'razorpay' => 'Razorpay',
                            'payu'     => 'PayU',
                            'easebuzz' => 'Easebuzz',
                            'phonepe'  => 'PhonePe',
                        ])
                        ->required()
                        ->disabledOn('edit')
                        ->live(),
                    TextInput::make('display_label')
                        ->label('Display Label')
                        ->placeholder('e.g. Stripe (Live)')
                        ->maxLength(100),
                    Toggle::make('is_active')->label('Active'),
                    Toggle::make('is_default')->label('Default')
                        ->helperText('Only one gateway can be default.'),
                ])->columns(2),

            Section::make('Common Settings')
                ->schema([
                    TextInput::make('base_url')
                        ->label('API Base URL')
                        ->placeholder('Auto-suggested per gateway')
                        ->datalist([
                            'https://api.stripe.com/v1',
                            'https://api.razorpay.com/v1',
                            'https://api-m.paypal.com',        // live
                            'https://api-m.sandbox.paypal.com',// sandbox
                            'https://api.cashfree.com/pg',
                            'https://sandbox.cashfree.com/pg',
                            'https://api.phonepe.com/apis/pg-sandbox',
                        ]),

                        Select::make('currency')
                        ->options([
                            'inr' => 'INR',
                            'usd' => 'USD',
                            'eur' => 'EUR',
                        ])->default('inr')->searchable(),
                    CheckboxList::make('methods')
                        ->label('Allowed Methods')
                        ->options([
                            'card' => 'Card',
                            'upi' => 'UPI',
                            'netbanking' => 'Netbanking',
                            'wallet' => 'Wallet',
                            'emi' => 'EMI',
                        ])->columns(3),
                    Placeholder::make('webhook_url')
                        ->label('Webhook Endpoint to set in provider')
                        ->content(fn (Get $get) => $get('name')
                            ? url("/api/payments/{$get('name')}/webhook")
                            : 'Select a gateway to see the URL'),
                ])->columns(2),

            Section::make('Credentials')
                ->description('Fields change based on the gateway you choose.')
                ->schema([
                    Fieldset::make('Razorpay')
                        ->visible(fn (Get $get) => $get('name') === 'razorpay')
                        ->schema([
                            TextInput::make('api_key')
                                ->label('Key ID')->password()->revealable()
                                ->required(fn (Get $get) => (bool) $get('is_active')),
                            TextInput::make('api_secret')
                                ->label('Key Secret')->password()->revealable()
                                ->required(fn (Get $get) => (bool) $get('is_active')),
                            TextInput::make('webhook_secret')
                                ->label('Webhook Secret')->password()->revealable(),
                            Select::make('meta.env')
                                ->label('Environment')
                                ->options(['live' => 'Live', 'test' => 'Test'])
                                ->default('test')
                                ->helperText('Driver will pick the base URL unless overridden above.'),
                        ]),
// ───────────────── PAYU ──────────────────
Fieldset::make('PayU')
    ->visible(fn (Get $get) => $get('name') === 'payu')
    ->schema([
        TextInput::make('meta.merchant_key')
            ->label('Merchant Key')
            ->password()
            ->revealable()
            // show a neutral sentinel instead of the real value
            ->afterStateHydrated(function (TextInput $component, $state) {
                if (filled($state)) {
                    $component->state('__KEEP__')
                        ->placeholder('•••• already set ••••')
                        ->hint('A value is already saved. Leave as-is to keep it.');
                }
            })
            // only write to DB if admin typed a new value
            ->dehydrated(fn ($state) => $state !== '__KEEP__' && filled($state))
            ->required(fn (Get $get) => (bool) $get('is_active')),

        TextInput::make('meta.salt')
            ->label('Salt')
            ->password()
            ->revealable()
            ->afterStateHydrated(function (TextInput $component, $state) {
                if (filled($state)) {
                    $component->state('__KEEP__')
                        ->placeholder('•••• already set ••••')
                        ->hint('A value is already saved. Leave as-is to keep it.');
                }
            })
            ->dehydrated(fn ($state) => $state !== '__KEEP__' && filled($state))
            ->required(fn (Get $get) => (bool) $get('is_active')),

        Select::make('meta.env')
            ->label('Environment')
            ->options(['live' => 'Live', 'test' => 'Test'])
            ->default('test'),

        TextInput::make('meta.success_url')
            ->label('Success Redirect URL (override)')
            ->url(),

        TextInput::make('meta.failure_url')
            ->label('Failure Redirect URL (override)')
            ->url(),
        ]),

                   // ─────────────── PHONEPE (optional) ─────
                    Fieldset::make('PhonePe')
                        ->visible(fn (Get $get) => $get('name') === 'phonepe')
                        ->schema([
                            TextInput::make('meta.merchant_id')
                                ->label('Merchant ID')->password()->revealable()
                                ->required(fn (Get $get) => (bool) $get('is_active')),
                            TextInput::make('meta.salt_key')
                                ->label('Salt Key')->password()->revealable()
                                ->required(fn (Get $get) => (bool) $get('is_active')),
                            TextInput::make('meta.salt_index')
                                ->label('Salt Index'),
                        ]),
                ])->columnSpan('full'),



           /* Section::make('Advanced / Extra Meta')
                ->schema([
                    KeyValue::make('meta')
                        ->label('Additional Meta (optional)')
                        ->keyLabel('Key')->valueLabel('Value')
                        ->addButtonLabel('Add'),
                ]),*/
        ])->columns(1)
          ->model(PaymentGatewaySetting::class);
          /*->rules([
              'name' => [
                  Rule::unique('payment_gateway_settings','name')->ignore(fn ($record) => $record?->id),
              ],
              // Example “required if active” validation per gateway
              'api_key' => function (callable $get) {
                  return $get('is_active') && in_array($get('name'), ['stripe','razorpay','cashfree','phonepe'])
                      ? 'required' : 'nullable';
              },
              'api_secret' => function (callable $get) {
                  return $get('is_active') && in_array($get('name'), ['stripe','razorpay'])
                      ? 'required' : 'nullable';
              },
            ]); */
    }
}
