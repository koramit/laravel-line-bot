<?php

namespace Koramit\LaravelLINEBot\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyLINEWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->route('botChannelSecret') !== config('line.bot_channel_secret')) {
            abort(404);
        }

        $hash = hash_hmac('sha256', $request->getContent(), config('line.bot_channel_secret'), true);
        $signature = base64_encode($hash);

        if ($request->header('x-line-signature') !== $signature) {
            abort(404);
        }

        return $next($request);
    }
}
