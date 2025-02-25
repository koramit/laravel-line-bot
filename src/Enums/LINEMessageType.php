<?php

namespace Koramit\LaravelLINEBot\Enums;

enum LINEMessageType: int
{
    case TEXT = 1;
    case IMAGE = 2;
    case VIDEO = 3;
    case AUDIO = 4;
    case FILE = 5;
    case LOCATION = 6;
    case STICKER = 7;

    public function label(): string
    {
        return match ($this) {
            self::TEXT => 'text',
            self::IMAGE => 'image',
            self::VIDEO => 'video',
            self::AUDIO => 'audio',
            self::FILE => 'file',
            self::LOCATION => 'location',
            self::STICKER => 'sticker',
        };
    }

    public static function fromLabel(string $label): self
    {
        return match ($label) {
            'text' => self::TEXT,
            'image' => self::IMAGE,
            'video' => self::VIDEO,
            'audio' => self::AUDIO,
            'file' => self::FILE,
            'location' => self::LOCATION,
            'sticker' => self::STICKER,
        };
    }
}
