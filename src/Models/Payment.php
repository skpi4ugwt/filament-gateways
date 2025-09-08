<?php

// src/Models/Payment.php
namespace Labify\Gateways\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $fillable = ['public_id','gateway','external_id','currency','amount','status','meta'];
    protected $casts = ['meta' => 'array'];

    protected static function booted() {
        static::creating(fn($p) => $p->public_id ??= (string) Str::uuid());
    }

    public function attempts() { return $this->hasMany(PaymentAttempt::class); }
}
