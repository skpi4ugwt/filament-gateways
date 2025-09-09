<?php

namespace App\Filament\Resources\PaymentGatewaySettings\Schemas;

use Illuminate\Validation\Rule as LaravelRule;
use Filament\Forms\Components\{
    TextInput,
    Toggle,
    Section,
    Select,
    CheckboxList,
    Placeholder,
    Fieldset,
    KeyValue
};
use Filament\Forms\Get as FormsGet;

class PaymentGatewaySettingForm
{
    public static function components(): array
    {
        return [
            Section::make('Gateway')
                ->schema([
                    Select::make('name')
                        ->label('Gateway')
                        ->options([
                            'razorpay' => 'Razorpay',
                            'payu'     => 'PayU',
                            'easebuzz' => 'Easebuzz',
                        ])
                        ->required()
                        ->live()
                        ->disabled(fn (string $operation) => $operation === 'edit')
                        ->rule(fn ($record) =>
                            LaravelRule::unique('payment_gateway_settings', 'name')->ignore($record?->id)
                        ),
                    TextInput::make('display_label')
                        ->label('Display Label')
                        ->placeholder('e.g. Razorpay (Live)')
                        ->maxLength(100),
                    Toggle::make('is_active')->label('Active'),
                    Toggle::make('is_default')->label('Default')
                        ->helperText('Only one gateway can be default.'),
                ])->columns(2),

            Section::make('Common Settings')
                ->schema([
                    TextInput::make('base_url')
                        ->label('API Base URL (optional override)')
                        ->placeholder('Leave blank to use driver defaults per environment')
                        ->datalist([
                            'https://api.razorpay.com/v1',
                            'https://secure.payu.in',
                            'https://test.payu.in',
                            'https://api.easebuzz.in',
                            'https://testpay.easebuzz.in',
                        ]),
                    Select::make('currency')
                        ->label('Default Currency')
                        ->options(['inr'=>'INR','usd'=>'USD','eur'=>'EUR'])
                        ->default('inr')
                        ->searchable(),
                    CheckboxList::make('methods')
                        ->label('Allowed Methods')
                        ->options([
                            'card'       => 'Card',
                            'upi'        => 'UPI',
                            'netbanking' => 'Netbanking',
                            'wallet'     => 'Wallet',
                            'emi'        => 'EMI',
                        ])->columns(3),
                    Placeholder::make('callback_hint')
                        ->label('Callback / Redirect / Webhook (copy to provider)')
                        ->content(function (FormsGet $get) {
                            $gw = $get('name') ?: 'gateway';
                            return
                                "Success URL: " . url("/payments/{$gw}/return/success") . "\n" .
                                "Failure URL: " . url("/payments/{$gw}/return/failure") . "\n" .
                                "Webhook URL: " . url("/api/payments/{$gw}/webhook");
                        }),
                ])->columns(2),

            Section::make('Credentials')
                ->description('Fields change based on the selected gateway.')
                ->schema([
                    // Razorpay (top-level secrets)
                    Fieldset::make('Razorpay')
                        ->visible(fn (FormsGet $get) => $get('name') === 'razorpay')
                        ->schema([
                            TextInput::make('api_key')
                                ->label('Key ID')->password()->revealable()
                                ->afterStateHydrated(function (TextInput $c, $state) {
                                    if (filled($state)) {
                                        $c->state('__KEEP__')->placeholder('•••• already set ••••')
                                          ->hint('Leave unchanged to keep existing value');
                                    }
                                })
                                ->dehydrated(fn ($s) => $s !== '__KEEP__' && filled($s))
                                ->required(fn (FormsGet $get) => (bool)$get('is_active')),
                            TextInput::make('api_secret')
                                ->label('Key Secret')->password()->revealable()
                                ->afterStateHydrated(function (TextInput $c, $state) {
                                    if (filled($state)) {
                                        $c->state('__KEEP__')->placeholder('•••• already set ••••')
                                          ->hint('Leave unchanged to keep existing value');
                                    }
                                })
                                ->dehydrated(fn ($s) => $s !== '__KEEP__' && filled($s))
                                ->required(fn (FormsGet $get) => (bool)$get('is_active')),
                            TextInput::make('webhook_secret')
                                ->label('Webhook Secret')->password()->revealable()
                                ->afterStateHydrated(function (TextInput $c, $state) {
                                    if (filled($state)) {
                                        $c->state('__KEEP__')->placeholder('•••• already set ••••')
                                          ->hint('Leave unchanged to keep existing value');
                                    }
                                })
                                ->dehydrated(fn ($s) => $s !== '__KEEP__' && filled($s)),
                        ]),

                    // PayU — bind to meta
                    Fieldset::make('PayU')
                        ->visible(fn (FormsGet $get) => $get('name') === 'payu')
                        ->statePath('meta')
                        ->schema([
                            TextInput::make('merchant_key')
                                ->label('Merchant Key')->password()->revealable()
                                ->afterStateHydrated(fn (TextInput $c, $s) => filled($s) && $c->state('__KEEP__')->placeholder('•••• already set ••••')->hint('Leave unchanged to keep existing value'))
                                ->dehydrated(fn ($s) => $s !== '__KEEP__' && filled($s))
                                ->required(fn (FormsGet $get) => (bool)$get('../../is_active')),
                            TextInput::make('salt')
                                ->label('Salt')->password()->revealable()
                                ->afterStateHydrated(fn (TextInput $c, $s) => filled($s) && $c->state('__KEEP__')->placeholder('•••• already set ••••')->hint('Leave unchanged to keep existing value'))
                                ->dehydrated(fn ($s) => $s !== '__KEEP__' && filled($s))
                                ->required(fn (FormsGet $get) => (bool)$get('../../is_active')),
                            Select::make('env')
                                ->label('Environment')
                                ->options(['live'=>'Live','test'=>'Test'])
                                ->default('test'),
                            TextInput::make('success_url')->label('Success Redirect URL (override)')->url(),
                            TextInput::make('failure_url')->label('Failure Redirect URL (override)')->url(),
                        ]),

                    // Easebuzz — bind to meta
                    Fieldset::make('Easebuzz')
                        ->visible(fn (FormsGet $get) => $get('name') === 'easebuzz')
                        ->statePath('meta')
                        ->schema([
                            TextInput::make('merchant_key')
                                ->label('Merchant Key')->password()->revealable()
                                ->afterStateHydrated(fn (TextInput $c, $s) => filled($s) && $c->state('__KEEP__')->placeholder('•••• already set ••••')->hint('Leave unchanged to keep existing value'))
                                ->dehydrated(fn ($s) => $s !== '__KEEP__' && filled($s))
                                ->required(fn (FormsGet $get) => (bool)$get('../../is_active')),
                            TextInput::make('salt')
                                ->label('Salt')->password()->revealable()
                                ->afterStateHydrated(fn (TextInput $c, $s) => filled($s) && $c->state('__KEEP__')->placeholder('•••• already set ••••')->hint('Leave unchanged to keep existing value'))
                                ->dehydrated(fn ($s) => $s !== '__KEEP__' && filled($s))
                                ->required(fn (FormsGet $get) => (bool)$get('../../is_active')),
                            TextInput::make('access_key')
                                ->label('Access Key (optional)')->password()->revealable()
                                ->afterStateHydrated(fn (TextInput $c, $s) => filled($s) && $c->state('__KEEP__')->placeholder('•••• already set ••••')->hint('Leave unchanged to keep existing value'))
                                ->dehydrated(fn ($s) => $s !== '__KEEP__' && filled($s)),
                            Select::make('env')
                                ->label('Environment')
                                ->options(['live'=>'Live','test'=>'Test'])
                                ->default('test'),
                        ]),
                ])->columns(2),

            // Optional read-only meta viewer (avoid clobbering structured meta)
            /*Section::make('Advanced / Extra Meta')
                ->schema([
                    KeyValue::make('meta')
                        ->label('Additional Meta (read-only)')
                        ->dehydrated(false)
                        ->keyLabel('Key')
                        ->valueLabel('Value'),
                ]), */
        ];
    }
}
