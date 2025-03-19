<?php

namespace Koramit\LaravelLINEBot\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Koramit\LaravelLINEBot\Enums\LINEEventType;

/**
 * @property int $id
 * @property LINEEventType $type
 * @property string|null $webhook_event_id
 * @property string|null $request_id
 * @property int $request_status
 * @property int $line_user_profile_id
 * @property int $user_id
 * @property \ArrayObject<array-key, mixed> $payload
 * @property \Illuminate\Support\Carbon|null $processed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LINEBotChatLog fromMessageId(string $messageId, ?int $lineProfileId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LINEBotChatLog fromReplyToken(string $replyToken, ?int $lineProfileId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LINEBotChatLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LINEBotChatLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LINEBotChatLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LINEBotChatLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LINEBotChatLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LINEBotChatLog whereLineUserProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LINEBotChatLog wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LINEBotChatLog whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LINEBotChatLog whereRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LINEBotChatLog whereRequestStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LINEBotChatLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LINEBotChatLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LINEBotChatLog whereWebhookEventId($value)
 *
 * @mixin \Eloquent
 */
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

    public function scopeFromMessageId(Builder $query, string $messageId, ?int $lineProfileId = null): void
    {
        $query->where('type', LINEEventType::MESSAGE)
            ->where('payload->message->id', $messageId)
            ->when($lineProfileId, fn ($q) => $q->where('line_user_profile_id', $lineProfileId));
    }

    public function scopeFromReplyToken(Builder $query, string $replyToken, ?int $lineProfileId = null): void
    {
        $query->whereIn('type', LINEEventType::withReplyToken())
            ->where('payload->replyToken', $replyToken)
            ->when($lineProfileId, fn ($q) => $q->where('line_user_profile_id', $lineProfileId));
    }
}
