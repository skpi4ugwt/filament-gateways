<?php

// database/migrations/0002_create_payment_gateway_settings_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('payment_gateway_settings', function (Blueprint $t) {
      $t->id();
      $t->string('name')->unique(); // razorpay|payu|easebuzz
      $t->string('display_label')->nullable();
      $t->boolean('is_active')->default(false);
      $t->boolean('is_default')->default(false);
      $t->string('base_url')->nullable();
      $t->string('currency', 10)->default('inr');
      $t->json('methods')->nullable();
      $t->json('meta')->nullable(); // env, keys, salts, etc.
      $t->string('api_key')->nullable();        // razorpay
      $t->string('api_secret')->nullable();     // razorpay
      $t->string('webhook_secret')->nullable(); // razorpay
      $t->timestamps();
    });
  }
  public function down(): void {
    Schema::dropIfExists('payment_gateway_settings');
  }
};
