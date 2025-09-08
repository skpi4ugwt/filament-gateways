<?php

// src/Models/PaymentAttempt.php
namespace Labify\Gateways\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAttempt extends Model
{
    protected $fillable = ['payment_id','action','result','http_code','message','payload'];
    protected $casts = ['payload' => 'array'];
}
