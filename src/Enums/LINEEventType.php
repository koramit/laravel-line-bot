<?php

namespace Koramit\LaravelLINEBot\Enums;

enum LINEEventType: int
{
    case REPLY = 1;
    case PUSH = 2;
    case MESSAGE = 3;
    case UNSEND = 4;
    case FOLLOW = 5;
    case UNFOLLOW = 6;

    public function label(): string
    {
        return match ($this) {
            self::REPLY => 'reply',
            self::PUSH => 'push',
            self::MESSAGE => 'message',
            self::UNSEND => 'unsend',
            self::FOLLOW => 'follow',
            self::UNFOLLOW => 'unfollow',
        };
    }

    public static function fromLabel(string $label): self
    {
        return match ($label) {
            'reply' => self::REPLY,
            'push' => self::PUSH,
            'message' => self::MESSAGE,
            'unsend' => self::UNSEND,
            'follow' => self::FOLLOW,
            'unfollow' => self::UNFOLLOW,
        };
    }
}
