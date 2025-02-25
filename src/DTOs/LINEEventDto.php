<?php

namespace Koramit\LaravelLINEBot\DTOs;

use Illuminate\Support\Carbon;
use Koramit\LaravelLINEBot\Enums\LINEEventType;
use Koramit\LaravelLINEBot\Enums\LINEMessageType;

readonly class LINEEventDto
{
    public string $sourceType;

    public ?string $userId;

    public LINEEventType $eventType;

    public bool $isRedelivery;

    public bool $isReadyToReply;

    public bool $isUnblocked;

    public string $webhookEventId;

    public Carbon $timestamp;

    public ?string $replyToken;

    public ?string $messageId;

    public ?LINEMessageType $messageType;

    public ?string $messageText;

    public ?string $messageQuoteToken;

    public ?string $messageImageSetId;

    public ?string $messageImageSetIndex;

    public ?string $messageImageSetTotal;

    public ?string $messageContentProvider;

    public ?string $messageOriginalContentUrl;

    public ?string $messagePreviewImageUrl;

    public function __construct(array $event)
    {
        $event = collect($event)->dot();
        $this->sourceType = $event['source.type'];
        $this->userId = $event['source.userId'] ?? null;
        $this->eventType = LINEEventType::fromLabel($event['type']);
        $this->isRedelivery = $event['deliveryContext.isRedelivery'];
        $this->isReadyToReply = $event['mode'] === 'active';
        $this->isUnblocked = $event['follow.isUnblocked'] ?? false;
        $this->webhookEventId = $event['webhookEventId'];
        $this->timestamp = Carbon::createFromTimestampMs($event['timestamp']);
        $this->replyToken = $event['replyToken'] ?? null;
        $this->messageId = $event['message.id'] ?? null;
        $this->messageType = ($event['message.type'] ?? null) ? LINEMessageType::fromLabel($event['message.type']) : null;
        $this->messageText = $event['message.text'] ?? null;
        $this->messageQuoteToken = $event['message.quoteToken'] ?? null;
        $this->messageImageSetId = $event['message.contentProvider.imageSet.id'] ?? null;
        $this->messageImageSetIndex = $event['message.contentProvider.imageSet.index'] ?? null;
        $this->messageImageSetTotal = $event['message.contentProvider.imageSet.total'] ?? null;
        $this->messageContentProvider = $event['message.contentProvider.type'] ?? null;
        $this->messageOriginalContentUrl = $event['message.contentProvider.originalContentUrl'] ?? null;
        $this->messagePreviewImageUrl = $event['message.contentProvider.previewImageUrl'] ?? null;
    }
}
