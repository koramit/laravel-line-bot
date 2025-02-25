<?php

namespace Koramit\LaravelLINEBot\Traits;

use Illuminate\Support\Carbon;
use Koramit\LaravelLINEBot\Enums\LINEEventType;
use Koramit\LaravelLINEBot\LINEMessageObject;
use Koramit\LaravelLINEBot\Models\LINEBotChatLog;

trait ChatLoggable
{
    protected function logReply(LINEBotChatLog $log, LINEMessageObject $messageObject): void
    {
        $log->processed_at = Carbon::now();
        $log->save();

        LINEBotChatLog::query()
            ->create([
                'line_user_profile_id' => $log->line_user_profile_id,
                'type' => LINEEventType::REPLY,
                'webhook_event_id' => $log->webhook_event_id,
                'processed_at' => Carbon::now(),
                'payload' => $messageObject->get(),
            ]);
    }
}
