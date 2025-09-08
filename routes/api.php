<?php

// routes/api.php
use Illuminate\Support\Facades\Route;
use Labify\Gateways\Http\Controllers\{PaymentController, WebhookController};

Route::prefix('api')->group(function () {
    Route::post('/payments/start', [PaymentController::class,'start']);
    Route::post('/payments/{publicId}/capture', [PaymentController::class,'capture']);
    Route::post('/payments/{publicId}/refund', [PaymentController::class,'refund']);
    Route::post('/payments/{gateway}/webhook', [WebhookController::class,'handle']);
});
