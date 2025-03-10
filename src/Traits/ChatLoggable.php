<?php

namespace Koramit\LaravelLINEBot\Traits;

use Illuminate\Support\Carbon;
use Koramit\LaravelLINEBot\Enums\LINEEventType;
use Koramit\LaravelLINEBot\LINEMessageObject;
use Koramit\LaravelLINEBot\Models\LINEBotChatLog;

trait ChatLoggable
{
    protected function logReply(LINEBotChatLog $log, LINEMessageObject $messageObject, ?array $responseJson = null): void
    {
        $log->processed_at = Carbon::now();
        $log->save();

        $payload = $messageObject->get();
        if ($responseJson && ($responseJson['sentMessages'] ?? false)) {
            foreach ($responseJson['sentMessages'] as $index => $sentMessage) {
                $payload[$index]['sentMessage'] = $sentMessage;
            }
        }

        LINEBotChatLog::query()
            ->create([
                'line_user_profile_id' => $log->line_user_profile_id,
                'type' => LINEEventType::REPLY,
                'webhook_event_id' => $log->webhook_event_id,
                'request_id' => $responseJson['request_id'] ?? null,
                'request_status' => $responseJson['request_status'] ?? null,
                'processed_at' => Carbon::now(),
                'payload' => $payload,
            ]);
    }
}
