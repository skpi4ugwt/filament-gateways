<?php

// src/Payments/Gateways/RazorpayGateway.php
namespace Labify\Gateways\Payments\Gateways;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Labify\Gateways\Models\{Payment, WebhookEvent};
use Labify\Gateways\Payments\Contracts\PaymentGateway;

class RazorpayGateway implements PaymentGateway
{
    public function __construct(private array $cfg) {}

    public function createIntent(Payment $payment, array $options = []): array
    {
        $base = rtrim($this->cfg['base_url'] ?: 'https://api.razorpay.com/v1', '/');

        $resp = Http::withBasicAuth($this->cfg['key'] ?? '', $this->cfg['secret'] ?? '')
            ->post($base.'/orders', [
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'receipt' => $payment->public_id,
                'notes' => ['payment_public_id' => $payment->public_id],
            ]);

        $payment->attempts()->create([
            'action' => 'create_intent',
            'result' => $resp->successful() ? 'success' : 'error',
            'http_code' => $resp->status(),
            'payload' => $resp->json(),
        ]);

        if ($resp->failed()) {
            $payment->update(['status' => 'failed']);
            return ['ok' => false, 'error' => 'order_create_failed'];
        }

        $data = $resp->json();
        $payment->update([
            'external_id' => $data['id'] ?? null,
            'status' => 'pending',
            'meta' => array_merge($payment->meta ?? [], ['order' => $data]),
        ]);

        return [
            'ok' => true,
            'payment' => $payment->refresh(),
            'client' => [
                'key_id' => $this->cfg['key'] ?? null,
                'order_id' => $data['id'] ?? null,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
            ],
        ];
    }

    public function capture(Payment $payment, array $options = []): array
    {
        $pid = $options['razorpay_payment_id'] ?? null;
        if (!$pid) return ['ok'=>false,'error'=>'missing_payment_id'];

        $base = rtrim($this->cfg['base_url'] ?: 'https://api.razorpay.com/v1', '/');
        $resp = Http::withBasicAuth($this->cfg['key'] ?? '', $this->cfg['secret'] ?? '')
            ->post($base."/payments/{$pid}/capture", [
                'amount' => $payment->amount,
                'currency' => $payment->currency,
            ]);

        $payment->attempts()->create([
            'action'=>'capture','result'=>$resp->successful()?'success':'error',
            'http_code'=>$resp->status(),'payload'=>$resp->json()
        ]);

        if ($resp->failed()) return ['ok'=>false,'error'=>'capture_failed'];
        $payment->update(['status' => 'paid']);
        return ['ok'=>true,'payment'=>$payment->refresh()];
    }

    public function refund(Payment $payment, int $amount, array $options = []): array
    {
        $pid = $options['razorpay_payment_id'] ?? null;
        if (!$pid) return ['ok'=>false,'error'=>'missing_payment_id'];

        $base = rtrim($this->cfg['base_url'] ?: 'https://api.razorpay.com/v1', '/');
        $resp = Http::withBasicAuth($this->cfg['key'] ?? '', $this->cfg['secret'] ?? '')
            ->post($base."/payments/{$pid}/refund", ['amount' => $amount]);

        $payment->attempts()->create([
            'action'=>'refund','result'=>$resp->successful()?'success':'error',
            'http_code'=>$resp->status(),'payload'=>$resp->json()
        ]);

        if ($resp->failed()) return ['ok'=>false,'error'=>'refund_failed'];
        $payment->update(['status'=>'refunded']);
        return ['ok'=>true,'payment'=>$payment->refresh()];
    }

    public function verifyAndNormalizeWebhook(Request $request): array
    {
        $payload = $request->getContent();
        $sig = $request->header('X-Razorpay-Signature') ?? '';
        $expected = hash_hmac('sha256', $payload, $this->cfg['webhook_secret'] ?? '');
        abort_unless(hash_equals($expected, $sig), 400, 'Invalid signature');

        $data = json_decode($payload, true) ?? [];
        return [
            'gateway' => 'razorpay',
            'event_id' => ($data['event'] ?? 'evt').':'.($data['payload']['payment']['entity']['id'] ?? Str::uuid()),
            'type' => $data['event'] ?? 'unknown',
            'object' => $data['payload'] ?? [],
            'raw' => $data,
            'signature' => $sig,
        ];
    }

    public function handleWebhook(array $event): void
    {
        if (WebhookEvent::where('gateway','razorpay')->where('event_id',$event['event_id'])->exists()) return;

        $record = WebhookEvent::create([
            'gateway'=>'razorpay','event_id'=>$event['event_id'],
            'signature'=>$event['signature'] ?? null,'payload'=>$event['raw'],
        ]);

        $receipt = data_get($event, 'object.order.entity.receipt');
        if ($receipt && $payment = Payment::where('public_id',$receipt)->first()) {
            match ($event['type']) {
                'payment.captured' => $payment->update(['status'=>'paid']),
                'payment.failed'   => $payment->update(['status'=>'failed']),
                default => null,
            };
            $payment->attempts()->create(['action'=>'webhook','result'=>'success','payload'=>$event]);
        }

        $record->update(['processed_at'=>now()]);
    }
}
