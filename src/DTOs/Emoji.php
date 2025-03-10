<?php

namespace Koramit\LaravelLINEBot\DTOs;

class Emoji
{
    public function __construct(
        public string $productId,
        public string $emojiId,
        public int $index = 0
    ) {}

    public function toArray(): array
    {
        return [
            'index' => $this->index,
            'productId' => $this->productId,
            'emojiId' => $this->emojiId,
        ];
    }

    public function toSubstitution(): array
    {
        return [
            'type' => 'emoji',
            'productId' => $this->productId,
            'emojiId' => $this->emojiId,
        ];
    }
}
