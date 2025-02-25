<?php

namespace Koramit\LaravelLINEBot;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Koramit\LaravelLINEBot\DTOs\LINEEventDto;
use Koramit\LaravelLINEBot\Enums\LINEEventType;
use Koramit\LaravelLINEBot\Events\DisconnectRequested;
use Koramit\LaravelLINEBot\Events\MessageReceived;
use Koramit\LaravelLINEBot\Events\UserFollowed;
use Koramit\LaravelLINEBot\Events\UserUnfollowed;
use Koramit\LaravelLINEBot\Models\LINEBotChatLog;
use Koramit\LaravelLINEBot\Models\LINEUserProfile;
use Random\RandomException;

class HandleWebhook
{
    /**
     * @throws RandomException
     */
    public function __invoke(array $payload): void
    {
        if (
            ! array_key_exists('events', $payload)
            || count($payload['events']) === 0
        ) {
            return;
        }

        $events = [];
        foreach ($payload['events'] as $event) {
            $dto = new LINEEventDto($event);

            $profile = LINEUserProfile::query()
                ->whereLineUserId($dto->userId)
                ->firstOrCreate(['line_user_id' => $dto->userId]);

            $log = new LINEBotChatLog;
            $log->type = $dto->eventType;
            $log->webhook_event_id = $dto->webhookEventId;
            $log->line_user_profile_id = $profile->id;
            $log->payload = $event;
            $log->save();

            $events[] = [
                'dto' => $dto,
                'log' => $log,
            ];
        }

        foreach ($events as $event) {
            match ($dto->eventType) {
                LINEEventType::FOLLOW => $this->handleFollow($event['dto'], $event['log']),
                LINEEventType::UNFOLLOW => $this->handleUnfollow($event['dto'], $event['log']),
                LINEEventType::MESSAGE => $this->handleMessage($event['dto'], $event['log']),
            };
        }
    }

    /**
     * @throws RandomException
     */
    protected function handleFollow(LINEEventDto $event, LINEBotChatLog $log): void
    {
        if ($profile = LINEUserProfile::query()->whereLineUserId($event->userId)->first()) {
            if (! $profile->verify_code) {
                $profile->verify_code = $this->genVerifyCode();
                $profile->save();
            }
            UserFollowed::dispatch($event, $profile, $log);

            return;
        }

        $profile = new LINEUserProfile;
        $profile->line_user_id = $event->userId;
        $profile->verify_code = $this->genVerifyCode();
        $profile->save();

        UserFollowed::dispatch($event, $profile, $log);
    }

    protected function handleUnfollow(LINEEventDto $event, LINEBotChatLog $log): void
    {
        $profile = LINEUserProfile::query()
            ->whereLineUserId($event->userId)
            ->first();

        $profile->unfollowed_at = Carbon::now();
        $profile->save();

        UserUnfollowed::dispatch($event, $profile, $log);
    }

    protected function handleMessage(LINEEventDto $event, LINEBotChatLog $log): void
    {
        $profile = LINEUserProfile::query()
            ->whereLineUserId($event->userId)
            ->first();

        /* check if disconnect command */
        if (
            $profile->verified_at
            && $profile->user_id
            && $event->messageType === 'text'
            && $event->messageText === config('line.bot_disconnect_command')
        ) {
            $profile->verify_code = $this->genVerifyCode();
            $profile->verified_at = null;
            $profile->save();
            DisconnectRequested::dispatch($event, $profile, $log);

            return;
        }

        MessageReceived::dispatch($event, $profile, $log);
    }

    protected function genVerifyCode(): string
    {
        $codeLength = (int) config('line.bot_verify_code_length');

        do {
            $randomCode = random_int(0, pow(10, $codeLength) - 1);
            $verifyCode = Str::padLeft($randomCode, $codeLength, '0');
            if (LINEUserProfile::query()->fromPendingVerifyCode($verifyCode)->exists()) {
                $verifyCode = null;
            }
        } while ($verifyCode === null);

        return $verifyCode;
    }
}
