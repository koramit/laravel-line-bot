<?php

namespace Koramit\LaravelLINEBot;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Koramit\LaravelLINEBot\DTOs\LINEEventDto;
use Koramit\LaravelLINEBot\DTOs\LINEMessagingAPIResponseDto;
use Koramit\LaravelLINEBot\Enums\LINEEventType;
use Koramit\LaravelLINEBot\Exceptions\LINEMessagingAPIRequestException;
use Koramit\LaravelLINEBot\Models\LINEBotChatLog;
use Koramit\LaravelLINEBot\Models\LINEUserProfile;

class LINEMessagingAPI
{
    protected string $lastCall = '';

    /**
     * @throws LINEMessagingAPIRequestException
     */
    public function push(string $lineUserId, LINEMessageObject $messages, bool $notificationDisabled = false): LINEMessagingAPIResponseDto
    {
        return $this->makePost(config('line.bot_push_endpoint'), [
            'to' => $lineUserId,
            'messages' => $messages->get(),
            'notificationDisabled' => $notificationDisabled,
        ]);
    }

    /**
     * @throws LINEMessagingAPIRequestException
     */
    public function reply(string $replyToken, LINEMessageObject $messages, bool $notificationDisabled = false): LINEMessagingAPIResponseDto
    {
        $this->lastCall = 'reply';

        return $this->makePost(config('line.bot_reply_endpoint'), [
            'replyToken' => $replyToken,
            'messages' => $messages->get(),
            'notificationDisabled' => $notificationDisabled,
        ]);
    }

    /**
     * @throws LINEMessagingAPIRequestException
     */
    public function replyOrPush(LINEEventDto $lineEventDto, LINEMessageObject $messages, bool $notificationDisabled = false): LINEMessagingAPIResponseDto
    {
        $response = $this->reply($lineEventDto->replyToken, $messages, $notificationDisabled);
        if (! ($response->status === 400) || ! ($response->data['message'] === 'Invalid reply token')) {
            return $response;
        }
        Log::notice('Invalid reply token webhook event id = {webhookEventId}', ['webhookEventId' => $lineEventDto->webhookEventId]);

        return $this->push($lineEventDto->userId, $messages, $notificationDisabled);
    }

    /**
     * @throws LINEMessagingAPIRequestException
     */
    public function loadingAnimationStart(string $lineUserId, int $loadingSeconds = 5): void
    {
        $this->makePost(config('line.bot_loading_animation_endpoint'), [
            'chatId' => $lineUserId,
            'loadingSeconds' => $loadingSeconds,
        ]);
    }

    public function validateMessageObject(LINEMessageObject $message): array
    {
        try {
            $response = $this->makePost(config('line.validate_message_object_endpoint'), [
                'messages' => $message->get(),
            ]);
        } catch (LINEMessagingAPIRequestException $e) {
            return $e->body;
        }

        return $response->data;
    }

    /**
     * @throws LINEMessagingAPIRequestException
     */
    protected function makePost(string $endpoint, array $body): LINEMessagingAPIResponseDto
    {
        $retryKey = Str::uuid()->toString();
        try {
            $response = Http::withToken(config('line.bot_channel_access_token'))
                ->acceptJson()
                ->connectTimeout(config('line.api_connect_timeout_seconds'))
                ->timeout(config('line.api_timeout_seconds'))
                ->retry(
                    config('line.api_retry_times'),
                    config('line.api_retry_delay_milliseconds'),
                    function (Exception $exception, PendingRequest $request) use ($endpoint, $retryKey) {
                        if ($exception->getCode() === 500) {
                            if ($endpoint !== config('line.bot_reply_endpoint')) {
                                $request->withHeader('X-Line-Retry-Key', $retryKey);
                            }

                            return true;
                        }

                        return $exception instanceof ConnectionException;
                    },
                    false)
                ->post($endpoint, $body);
        } catch (Exception $e) {
            throw new LINEMessagingAPIRequestException($e->getMessage(), $e->getCode(), []);
        }

        return new LINEMessagingAPIResponseDto(
            $response->status(),
            $response->header('X-Line-Request-Id'),
            $response->json()
        );
    }

    public function logPush(LINEUserProfile $profile, LINEMessageObject $messageObject, LINEMessagingAPIResponseDto $response): void
    {
        LINEBotChatLog::query()
            ->create([
                'line_user_profile_id' => $profile->id,
                'user_id' => $profile->user_id,
                'type' => LINEEventType::PUSH,
                'request_id' => $response->lineRequestId,
                'request_status' => $response->status,
                'processed_at' => now(),
                'payload' => $this->mergeRequestResponseToSentMessages($messageObject, $response),
            ]);
    }

    public function logReply(string $webhookEventId, LINEUserProfile $profile, LINEMessageObject $messageObject, LINEMessagingAPIResponseDto $response): void
    {
        LINEBotChatLog::query()
            ->create([
                'line_user_profile_id' => $profile->id,
                'user_id' => $profile->user_id,
                'type' => LINEEventType::REPLY,
                'webhook_event_id' => $webhookEventId,
                'request_id' => $response->lineRequestId,
                'request_status' => $response->status,
                'processed_at' => now(),
                'payload' => $this->mergeRequestResponseToSentMessages($messageObject, $response),
            ]);
    }

    public function logReplyOrPush(string $webhookEventId, LINEUserProfile $profile, LINEMessageObject $messageObject, LINEMessagingAPIResponseDto $response): void
    {
        if ($this->lastCall === 'reply') {
            $this->logReply($webhookEventId, $profile, $messageObject, $response);
            $this->lastCall = '';

            return;
        }

        LINEBotChatLog::query()
            ->create([
                'line_user_profile_id' => $profile->id,
                'user_id' => $profile->user_id,
                'type' => LINEEventType::REPLY,
                'webhook_event_id' => $webhookEventId,
                'request_id' => null,
                'request_status' => 400,
                'processed_at' => now(),
                'payload' => $messageObject->get(),
            ]);

        $this->logPush($profile, $messageObject, $response);
    }

    protected function mergeRequestResponseToSentMessages(LINEMessageObject $messageObject, LINEMessagingAPIResponseDto $response): array
    {
        $payload = $messageObject->get();
        foreach ($response->data['sentMessages'] as $index => $sentMessage) {
            $payload[$index]['sentMessage'] = $sentMessage;
        }

        return $payload;
    }
}
