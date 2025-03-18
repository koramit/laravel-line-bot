<?php

namespace Koramit\LaravelLINEBot;

use Koramit\LaravelLINEBot\DTOs\LINEEventDto;
use Koramit\LaravelLINEBot\Enums\LINEEventType;
use Koramit\LaravelLINEBot\Events\AccountLinked;
use Koramit\LaravelLINEBot\Events\BeaconConnected;
use Koramit\LaravelLINEBot\Events\BotJoined;
use Koramit\LaravelLINEBot\Events\BotWasKicked;
use Koramit\LaravelLINEBot\Events\MemberJoined;
use Koramit\LaravelLINEBot\Events\MemberLeft;
use Koramit\LaravelLINEBot\Events\MembershipUpdated;
use Koramit\LaravelLINEBot\Events\MessageReceived;
use Koramit\LaravelLINEBot\Events\MessageUnsent;
use Koramit\LaravelLINEBot\Events\PostbackReceived;
use Koramit\LaravelLINEBot\Events\UserFollowed;
use Koramit\LaravelLINEBot\Events\UserUnfollowed;
use Koramit\LaravelLINEBot\Events\VideoViewCompleted;
use Koramit\LaravelLINEBot\Models\LINEBotChatLog;
use Koramit\LaravelLINEBot\Models\LINEUserProfile;

class HandleWebhook
{
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

            $profile = LINEUserProfile::query()->firstOrCreate(['line_user_id' => $dto->userId]);

            if (! $profile->verify_code) {
                $profile->genVerifyCode();
                $profile->save();
            }

            $log = new LINEBotChatLog;
            $log->type = $dto->eventType;
            $log->webhook_event_id = $dto->webhookEventId;
            $log->line_user_profile_id = $profile->id;
            $log->payload = $event;
            $log->save();

            $events[] = [
                'dto' => $dto,
                'profile' => $profile,
                'log' => $log,
            ];
        }

        foreach ($events as $event) {
            match ($event['dto']->eventType) {
                LINEEventType::FOLLOW => UserFollowed::dispatch($event['dto'], $event['profile'], $event['log']),
                LINEEventType::UNFOLLOW => UserUnfollowed::dispatch($event['dto'], $event['profile'], $event['log']),
                LINEEventType::MESSAGE => MessageReceived::dispatch($event['dto'], $event['profile'], $event['log']),
                LINEEventType::UNSEND => MessageUnsent::dispatch($event['dto'], $event['profile'], $event['log']),
                LINEEventType::JOIN => BotJoined::dispatch($event['dto'], $event['profile'], $event['log']),
                LINEEventType::LEAVE => BotWasKicked::dispatch($event['dto'], $event['profile'], $event['log']),
                LINEEventType::MEMBER_JOIN => MemberJoined::dispatch($event['dto'], $event['profile'], $event['log']),
                LINEEventType::MEMBER_LEAVE => MemberLeft::dispathc($event['dto'], $event['profile'], $event['log']),
                LINEEventType::POSTBACK => PostbackReceived::dispatch($event['dto'], $event['profile'], $event['log']),
                LINEEventType::VIDEO_VIEWING_COMPLETE => VideoViewCompleted::dispatch($event['dto'], $event['profile'], $event['log']),
                LINEEventType::BEACON => BeaconConnected::dispatch($event['dto'], $event['profile'], $event['log']),
                LINEEventType::ACCOUNT_LINK => AccountLinked::dispatch($event['dto'], $event['profile'], $event['log']),
                LINEEventType::MEMBERSHIP => MembershipUpdated::dispatch($event['dto'], $event['profile'], $event['log']),
                default => null
            };
        }
    }
}
