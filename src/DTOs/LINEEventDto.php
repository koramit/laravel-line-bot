<?php

namespace Koramit\LaravelLINEBot\DTOs;

use Illuminate\Support\Carbon;
use Koramit\LaravelLINEBot\Enums\LINEEventType;
use Koramit\LaravelLINEBot\Enums\LINEMessageType;

readonly class LINEEventDto
{
    public string $sourceType;

    public ?string $userId;

    public ?string $groupId;

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

    public ?string $messageQuotedMessageId;

    public ?string $messageImageSetId;

    public ?string $messageImageSetIndex;

    public ?string $messageImageSetTotal;

    public ?string $messageContentProvider;

    public ?string $messageOriginalContentUrl;

    public ?string $messagePreviewImageUrl;

    public ?array $joined;

    public ?array $left;

    public ?array $postback;

    public ?string $videoPlayCompleteTrackingId;

    public ?array $beacon;

    public ?array $accountLink;

    public ?array $membership;

    public function __construct(array $event)
    {
        $flatten = collect($event)->dot();
        $this->sourceType = $flatten['source.type'];
        $this->userId = $flatten['source.userId'] ?? null;
        $this->groupId = $flatten['source.groupId'] ?? null;
        $this->eventType = LINEEventType::fromLabel($flatten['type']);
        $this->isRedelivery = $flatten['deliveryContext.isRedelivery'];
        $this->isReadyToReply = $flatten['mode'] === 'active';
        $this->isUnblocked = $flatten['follow.isUnblocked'] ?? false;
        $this->webhookEventId = $flatten['webhookEventId'];
        $this->timestamp = Carbon::createFromTimestampMs($flatten['timestamp']);
        $this->replyToken = $flatten['replyToken'] ?? null;
        $this->messageId = $flatten['message.id'] ?? null;
        $this->messageType = ($flatten['message.type'] ?? null) ? LINEMessageType::fromLabel($flatten['message.type']) : null;
        $this->messageText = $flatten['message.text'] ?? null;
        $this->messageQuoteToken = $flatten['message.quoteToken'] ?? null;
        $this->messageQuotedMessageId = $flatten['message.quotedMessageId'] ?? null;
        $this->messageImageSetId = $flatten['message.contentProvider.imageSet.id'] ?? null;
        $this->messageImageSetIndex = $flatten['message.contentProvider.imageSet.index'] ?? null;
        $this->messageImageSetTotal = $flatten['message.contentProvider.imageSet.total'] ?? null;
        $this->messageContentProvider = $flatten['message.contentProvider.type'] ?? null;
        $this->messageOriginalContentUrl = $flatten['message.contentProvider.originalContentUrl'] ?? null;
        $this->messagePreviewImageUrl = $flatten['message.contentProvider.previewImageUrl'] ?? null;
        $this->joined = $event['joined'] ?? null;
        $this->left = $event['left'] ?? null;
        $this->postback = $event['postback'] ?? null;
        $this->videoPlayCompleteTrackingId = $flatten['videoPlayComplete.trackingId'] ?? null;
        $this->beacon = $event['beacon'] ?? null;
        $this->accountLink = $event['link'] ?? null;
        $this->membership = $event['membership'] ?? null;
    }
}
