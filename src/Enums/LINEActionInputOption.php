<?php

namespace Koramit\LaravelLINEBot\Enums;

enum LINEActionInputOption: string
{
    case CLOSE_RICH_MENU = 'closeRichMenu';
    case OPEN_RICH_MENU = 'openRichMenu';
    case OPEN_KEYBOARD = 'openKeyboard';
    case OPEN_VOICE = 'openVoice';
}
