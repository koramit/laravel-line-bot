<?php

namespace Koramit\LaravelLINEBot\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Koramit\LaravelLINEBot\HandleWebhook;
use Random\RandomException;

class LINEWebhookController
{
    public function __invoke(Request $request, HandleWebhook $action)
    {
        defer(/**
         * @throws RandomException
         */ fn () => $action($request->all()));

        return new JsonResponse(status: 204);
    }
}
