<?php

// database/migrations/0001_create_payments_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('payments', function (Blueprint $t) {
      $t->id();
      $t->uuid('public_id')->unique();
      $t->string('gateway');
      $t->string('external_id')->nullable();
      $t->string('currency', 10);
      $t->bigInteger('amount');
      $t->string('status')->default('pending'); // pending|requires_action|paid|failed|refunded|canceled
      $t->json('meta')->nullable();
      $t->timestamps();
    });

    Schema::create('payment_attempts', function (Blueprint $t) {
      $t->id();
      $t->foreignId('payment_id')->constrained()->cascadeOnDelete();
      $t->string('action');  // create_intent|capture|refund|webhook
      $t->string('result')->nullable(); // success|error
      $t->integer('http_code')->nullable();
      $t->text('message')->nullable();
      $t->json('payload')->nullable();
      $t->timestamps();
    });

    Schema::create('webhook_events', function (Blueprint $t) {
      $t->id();
      $t->string('gateway');
      $t->string('event_id')->nullable();
      $t->string('signature')->nullable();
      $t->json('payload');
      $t->timestamp('processed_at')->nullable();
      $t->timestamps();
      $t->unique(['gateway','event_id']);
    });
  }
  public function down(): void {
    Schema::dropIfExists('webhook_events');
    Schema::dropIfExists('payment_attempts');
    Schema::dropIfExists('payments');
  }
};
