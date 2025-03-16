<?php

namespace Koramit\LaravelLINEBot\Enums;

enum LINEEventType: int
{
    case PUSH = 1;
    case REPLY = 2;
    case COMMAND = 3;
    case MESSAGE = 4;
    case UNSEND = 5;
    case FOLLOW = 6;
    case UNFOLLOW = 7;
    case JOIN = 8;
    case LEAVE = 9;
    case MEMBER_JOIN = 10;
    case MEMBER_LEAVE = 11;
    case POSTBACK = 12;
    case VIDEO_VIEWING_COMPLETE = 13;
    case BEACON = 14;
    case ACCOUNT_LINK = 15;
    case MEMBERSHIP = 16;

    public function label(): string
    {
        return match ($this) {
            self::PUSH => 'push',
            self::REPLY => 'reply',
            self::COMMAND => 'command',
            self::MESSAGE => 'message',
            self::UNSEND => 'unsend',
            self::FOLLOW => 'follow',
            self::UNFOLLOW => 'unfollow',
            self::JOIN => 'join',
            self::LEAVE => 'leave',
            self::MEMBER_JOIN => 'memberJoined',
            self::MEMBER_LEAVE => 'memberLeft',
            self::POSTBACK => 'postback',
            self::VIDEO_VIEWING_COMPLETE => 'videoPlayComplete',
            self::BEACON => 'beacon',
            self::ACCOUNT_LINK => 'accountLink',
            self::MEMBERSHIP => 'membership',
        };
    }

    public static function fromLabel(string $label): self
    {
        return match ($label) {
            'push' => self::PUSH,
            'reply' => self::REPLY,
            'command' => self::COMMAND,
            'message' => self::MESSAGE,
            'unsend' => self::UNSEND,
            'follow' => self::FOLLOW,
            'unfollow' => self::UNFOLLOW,
            'join' => self::JOIN,
            'leave' => self::LEAVE,
            'memberJoined' => self::MEMBER_JOIN,
            'memberLeft' => self::MEMBER_LEAVE,
            'postback' => self::POSTBACK,
            'videoPlayComplete' => self::VIDEO_VIEWING_COMPLETE,
            'beacon' => self::BEACON,
            'accountLink' => self::ACCOUNT_LINK,
            'membership' => self::MEMBERSHIP,
        };
    }

    public static function withoutReplyToken(): array
    {
        return [
            self::PUSH,
            self::REPLY,
            self::COMMAND,
            self::UNSEND,
            self::UNFOLLOW,
            self::LEAVE,
            self::MEMBER_LEAVE,
        ];
    }

    public static function withReplyToken(): array
    {
        $cases = [];
        foreach (LINEEventType::cases() as $case) {
            if (! in_array($case, LINEEventType::withoutReplyToken())) {
                $cases[] = $case;
            }
        }

        return $cases;
    }
}
