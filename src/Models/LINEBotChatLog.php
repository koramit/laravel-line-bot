<?php

namespace Koramit\LaravelLINEBot\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Koramit\LaravelLINEBot\Enums\LINEEventType;

class LINEBotChatLog extends Model
{
    protected $table = 'line_bot_chat_logs';

    protected function casts(): array
    {
        return [
            'type' => LINEEventType::class,
            'payload' => AsArrayObject::class,
            'processed_at' => 'datetime',
        ];
    }
}
