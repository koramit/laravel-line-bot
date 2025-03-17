<?php

namespace Koramit\LaravelLINEBot\Traits;

use Illuminate\Support\Carbon;
use Koramit\LaravelLINEBot\Enums\LINEEventType;
use Koramit\LaravelLINEBot\LINEMessageObject;
use Koramit\LaravelLINEBot\Models\LINEBotChatLog;
use Koramit\LaravelLINEBot\Models\LINEUserProfile;

trait ChatLoggable
{
    protected function logReply(LINEBotChatLog $log, LINEMessageObject $messageObject, ?array $responseJson = null): void
    {
        $log->processed_at = Carbon::now();
        $log->save();

        LINEBotChatLog::query()
            ->create([
                'line_user_profile_id' => $log->line_user_profile_id,
                'type' => LINEEventType::REPLY,
                'webhook_event_id' => $log->webhook_event_id,
                'request_id' => $responseJson['request_id'] ?? null,
                'request_status' => $responseJson['request_status'] ?? null,
                'processed_at' => Carbon::now(),
                'payload' => $this->mergeRequestResponseToSentMessages($messageObject, $responseJson),
            ]);
    }

    protected function logPush(LINEUserProfile $profile, LINEMessageObject $messageObject, ?array $responseJson = null): void
    {
        LINEBotChatLog::query()
            ->create([
                'line_user_profile_id' => $profile->id,
                'type' => LINEEventType::PUSH,
                'request_id' => $responseJson['request_id'] ?? null,
                'request_status' => $responseJson['request_status'] ?? null,
                'processed_at' => Carbon::now(),
                'payload' => $this->mergeRequestResponseToSentMessages($messageObject, $responseJson),
            ]);
    }

    protected function logReplyOrPush(LINEUserProfile $profile, LINEMessageObject $messageObject, ?array $responseJson = null): void
    {
        LINEBotChatLog::query()
            ->create([
                'line_user_profile_id' => $profile->id,
                'type' => LINEEventType::REPLY,
                'request_id' => null,
                'request_status' => 400,
                'processed_at' => Carbon::now(),
                'payload' => $messageObject->get(),
            ]);

        $this->logPush($profile, $messageObject, $responseJson);
    }

    protected function mergeRequestResponseToSentMessages(LINEMessageObject $messageObject, ?array $responseJson = null): array
    {
        $payload = $messageObject->get();
        if ($responseJson && ($responseJson['sentMessages'] ?? false)) {
            foreach ($responseJson['sentMessages'] as $index => $sentMessage) {
                $payload[$index]['sentMessage'] = $sentMessage;
            }
        }

        return $payload;
    }
}
