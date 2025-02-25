<?php

namespace Koramit\LaravelLINEBot;

use Illuminate\Support\Facades\Http;

class LINEMessagingAPI
{
    public function reply(string $replyToken, LINEMessageObject $messages): void
    {
        Http::withToken(config('line.bot_channel_access_token'))
            ->acceptJson()
            ->post(config('line.bot_reply_endpoint'), [
                'replyToken' => $replyToken,
                'messages' => $messages->get(),
            ]);
    }
}
