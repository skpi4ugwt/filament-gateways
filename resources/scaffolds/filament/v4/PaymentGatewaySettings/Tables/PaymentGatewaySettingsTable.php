<?php

namespace App\Filament\Resources\PaymentGatewaySettings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\Action;
use App\Models\PaymentGatewaySetting;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class PaymentGatewaySettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('display_label')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->boolean(),
                IconColumn::make('is_default')
                    ->boolean(),
                TextColumn::make('base_url')
                    ->searchable(),
                TextColumn::make('currency')
                    ->searchable(),
                //TextColumn::make('api_key')
                    //->searchable(),
                //TextColumn::make('api_secret')
                    //->searchable(),
                //TextColumn::make('webhook_secret')
                    //->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
                TernaryFilter::make('is_default')->label('Default'),
            ])
           ->recordActions([
    Action::make('test')
        ->label('Test Connection')
        ->icon('heroicon-o-wifi')
        ->action(function (PaymentGatewaySetting $record) {
            $name = $record->name;
            $ok = false;
            $note = '';

            // Small helper to see if a base URL is reachable (200/3xx/401/403/400 are all “reachable”)
            $isReachable = function (?string $url): array {
                if (empty($url)) return [false, 'Missing base URL'];
                try {
                    $resp = Http::timeout(6)->withHeaders(['Accept' => 'application/json'])->get(rtrim($url, '/'));
                    $reachable = $resp->successful() || in_array($resp->status(), [301,302,400,401,403], true);
                    return [$reachable, 'HTTP '.$resp->status()];
                } catch (\Throwable $e) {
                    return [false, $e->getMessage()];
                }
            };

            try {
                if ($name === 'razorpay') {
                    // Defaults if you didn't override base_url
                    $base = rtrim($record->base_url ?: 'https://api.razorpay.com/v1', '/');
                    // Basic Auth with Key ID / Key Secret
                    $res = Http::timeout(8)
                        ->withBasicAuth((string) $record->api_key, (string) $record->api_secret)
                        ->get($base . '/orders', ['count' => 1]);

                    // If credentials are wrong => 401 Unauthorized
                    $ok = $res->status() !== 401;
                    $note = 'Razorpay: GET /orders → HTTP '.$res->status();

                } elseif ($name === 'payu') {
                    // Pick sensible default base by env if not overridden
                    $env = $record->meta['env'] ?? 'test';
                    $base = $record->base_url ?: ($env === 'live' ? 'https://secure.payu.in' : 'https://test.payu.in');

                    $hasKeys = !empty($record->meta['merchant_key'] ?? null) && !empty($record->meta['salt'] ?? null);
                    [$reachable, $why] = $isReachable($base);

                    $ok = $hasKeys && $reachable;
                    $note = 'PayU: ' . ($hasKeys ? 'keys present; ' : 'keys missing; ') . 'base ' . ($reachable ? 'reachable (' . $why . ')' : 'not reachable ('.$why.')');

                    // (PayU APIs require signed payloads per operation; this test is intentionally non-invasive.)

                } elseif ($name === 'easebuzz') {
                    $env = $record->meta['env'] ?? 'test';
                    // Easebuzz common bases
                    $base = $record->base_url ?: ($env === 'live' ? 'https://api.easebuzz.in' : 'https://testpay.easebuzz.in');

                    $hasKeys = !empty($record->meta['merchant_key'] ?? null) && !empty($record->meta['salt'] ?? null);
                    // access_key is optional in some flows; don't force it
                    [$reachable, $why] = $isReachable($base);

                    $ok = $hasKeys && $reachable;
                    $note = 'Easebuzz: ' . ($hasKeys ? 'keys present; ' : 'keys missing; ') . 'base ' . ($reachable ? 'reachable (' . $why . ')' : 'not reachable ('.$why.')');
                } else {
                    $ok = false;
                    $note = 'Unsupported gateway in this action. Choose Razorpay, PayU, or Easebuzz.';
                }
            } catch (\Throwable $e) {
                $ok = false;
                $note = $e->getMessage();
            }

            if ($ok) {
                Notification::make()
                    ->success()
                    ->title('Connection OK')
                    ->body($note)
                    ->send();
            } else {
                Notification::make()
                    ->danger()
                    ->title('Connection Failed')
                    ->body($note ?: 'Check credentials / base URL / environment')
                    ->send();
            }
        }),
    EditAction::make(),
])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
