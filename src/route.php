<?php

use Illuminate\Support\Facades\Route;
use Koramit\LaravelLINEBot\Http\Controllers\LINEWebhookController;
use Koramit\LaravelLINEBot\Http\Middleware\VerifyLINEWebhook;

Route::middlewareGroup('line-webhook', [VerifyLINEWebhook::class]);

Route::middleware('line-webhook')
    ->group(function () {
        Route::post('/line-webhook/{botChannelSecret}', LINEWebhookController::class);
    });
