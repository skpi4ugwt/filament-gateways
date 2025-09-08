<?php

// src/Http/Controllers/PaymentController.php
namespace Labify\Gateways\Http\Controllers;

use Illuminate\Http\Request;
use Labify\Gateways\Models\Payment;
use Labify\Gateways\Payments\PaymentService;

class PaymentController
{
    public function __construct(private PaymentService $svc) {}

    public function start(Request $req)
    {
        $data = $req->validate([
            'gateway' => 'nullable|string',
            'amount' => 'required|integer|min:1',
            'currency' => 'required|string|size:3',
            'options' => 'array',
        ]);
        return response()->json($this->svc->start(
            $data['gateway'] ?? null, $data['amount'], $data['currency'], $data['options'] ?? []
        ));
    }

    public function capture(Request $req, string $publicId)
    {
        $payment = Payment::where('public_id',$publicId)->firstOrFail();
        $data = $req->validate(['options' => 'array']);
        return response()->json($this->svc->capture($payment, $data['options'] ?? []));
    }

    public function refund(Request $req, string $publicId)
    {
        $payment = Payment::where('public_id',$publicId)->firstOrFail();
        $data = $req->validate(['amount'=>'required|integer|min:1','options'=>'array']);
        return response()->json($this->svc->refund($payment, $data['amount'], $data['options'] ?? []));
    }
}
