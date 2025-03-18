<?php

namespace Koramit\LaravelLINEBot;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Koramit\LaravelLINEBot\DTOs\LINEEventDto;
use Koramit\LaravelLINEBot\Enums\LINEEventType;
use Koramit\LaravelLINEBot\Exceptions\LINEMessagingAPIRequestException;
use Koramit\LaravelLINEBot\Models\LINEBotChatLog;

class LINEMessagingAPI
{
    /**
     * @throws LINEMessagingAPIRequestException
     */
    public function push(string $lineUserId, LINEMessageObject $messages, bool $notificationDisabled = false): array
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
    public function reply(string $replyToken, LINEMessageObject $messages, bool $notificationDisabled = false): array
    {
        return $this->makePost(config('line.bot_reply_endpoint'), [
            'replyToken' => $replyToken,
            'messages' => $messages->get(),
            'notificationDisabled' => $notificationDisabled,
        ]);
    }

    /**
     * @throws LINEMessagingAPIRequestException
     */
    public function replyOrPush(LINEEventDto $eventDto, LINEMessageObject $messages, bool $notificationDisabled = false): array
    {
        try {
            $data = $this->reply($eventDto->replyToken, $messages, $notificationDisabled);
            $data['sent_by'] = 'reply';

            return $data;
        } catch (LINEMessagingAPIRequestException $e) {
            if (! ($e->getCode() === 400) || ! ($e->getMessage() === 'Invalid reply token')) {
                throw $e;
            }

            Log::notice('Invalid reply token webhook event id = {webhookEventId}', ['webhookEventId' => $eventDto->webhookEventId]);

            $data = $this->push($eventDto->userId, $messages, $notificationDisabled);
            $data['sent_by'] = 'push';

            return $data;
        }
    }

    public function loadingAnimationStart(string $lineUserId, int $loadingSeconds = 5): void
    {
        try {
            $this->makePost(config('line.bot_loading_animation_endpoint'), [
                'chatId' => $lineUserId,
                'loadingSeconds' => $loadingSeconds,
            ]);
        } catch (LINEMessagingAPIRequestException $e) {
            if ($e->getCode() === 202) {
                return;
            }
            Log::error($e->getMessage());
        }
    }

    public function validateMessageObject(LINEMessageObject $message): array
    {
        try {
            $this->makePost(config('line.validate_message_object_endpoint'), [
                'messages' => $message->get(),
            ]);
        } catch (LINEMessagingAPIRequestException $e) {
            return $e->body;
        }

        return [];
    }

    /**
     * @throws LINEMessagingAPIRequestException
     */
    protected function makePost(string $endpoint, array $body): array
    {
        $retryKey = Str::uuid()->toString();
        try {
            $response = Http::withToken(config('line.bot_channel_access_token'))
                ->acceptJson()
                ->timeout(config('line.api_timeout_seconds'))
                ->retry(
                    config('line.api_retry_times'),
                    200,
                    function (Exception $exception, PendingRequest $request) use ($endpoint, $retryKey) {
                        if ($exception->getCode() === 500) {
                            if ($endpoint !== config('line.bot_reply_endpoint')) {
                                $request->withHeader('X-Line-Retry-Key', $retryKey);
                            }

                            return true;
                        }

                        return $exception instanceof ConnectionException
                            && str_contains($exception->getMessage(), 'timed out after');
                    },
                    throw: false)
                ->post($endpoint, $body);
        } catch (Exception $e) {
            throw new LINEMessagingAPIRequestException($e->getMessage(), $e->getCode(), []);
        }

        $data = $response->json();
        $data['request_id'] = $response->header('X-Line-Request-Id');
        $data['request_status'] = $response->status();

        if ($response->status() !== 200) {
            throw new LINEMessagingAPIRequestException($data['message'] ?? 'LINE API request error', $response->status(), $data);
        }

        return $data;
    }

    public function logPush(string $lineUserProfileId, LINEMessageObject $messageObject, array $responseJson): void
    {
        LINEBotChatLog::query()
            ->create([
                'line_user_profile_id' => $lineUserProfileId,
                'type' => LINEEventType::PUSH,
                'request_id' => $responseJson['request_id'] ?? null,
                'request_status' => $responseJson['request_status'],
                'processed_at' => now(),
                'payload' => $this->mergeRequestResponseToSentMessages($messageObject, $responseJson),
            ]);
    }

    public function logReply(LINEBotChatLog $log, LINEMessageObject $messageObject, array $responseJson): void
    {
        $log->touch('processed_at');

        LINEBotChatLog::query()
            ->create([
                'line_user_profile_id' => $log->line_user_profile_id,
                'type' => LINEEventType::REPLY,
                'webhook_event_id' => $log->webhook_event_id,
                'request_id' => $responseJson['request_id'] ?? null,
                'request_status' => $responseJson['request_status'],
                'processed_at' => now(),
                'payload' => $this->mergeRequestResponseToSentMessages($messageObject, $responseJson),
            ]);
    }

    public function logReplyOrPush(LINEBotChatLog $log, string $lineUserProfileId, LINEMessageObject $messageObject, array $responseJson): void
    {
        if ($responseJson['sent_by'] === 'reply') {
            $this->logReply($log, $messageObject, $responseJson);

            return;
        }

        LINEBotChatLog::query()
            ->create([
                'line_user_profile_id' => $lineUserProfileId,
                'type' => LINEEventType::REPLY,
                'webhook_event_id' => $log->webhook_event_id,
                'request_id' => null,
                'request_status' => 400,
                'processed_at' => now(),
                'payload' => $messageObject->get(),
            ]);

        $this->logPush($lineUserProfileId, $messageObject, $responseJson);
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
