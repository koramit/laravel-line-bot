<?php

namespace Koramit\LaravelLINEBot;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Koramit\LaravelLINEBot\Exceptions\LINEMessagingAPIRequestException;

class LINEMessagingAPI
{
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
    public function push(string $lineUserId, LINEMessageObject $messages, bool $notificationDisabled = false): array
    {
        return $this->makePost(config('line.bot_push_endpoint'), [
            'to' => $lineUserId,
            'messages' => $messages->get(),
            'notificationDisabled' => $notificationDisabled,
        ]);
    }

    public function loadingAnimationStart(string $lineUserId, int $loadingSeconds = 5): void
    {
        Http::withToken(config('line.bot_channel_access_token'))
            ->acceptJson()
            ->timeout(config('line.api_timeout_seconds'))
            ->post(config('line.bot_loading_animation_endpoint'), [
                'chatId' => $lineUserId,
                'loadingSeconds' => $loadingSeconds,
            ]);
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
}
