<?php

// src/Models/WebhookEvent.php
namespace Labify\Gateways\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    protected $fillable = ['gateway','event_id','signature','payload','processed_at'];
    protected $casts = ['payload' => 'array', 'processed_at' => 'datetime'];
}
