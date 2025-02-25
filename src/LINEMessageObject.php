<?php

namespace Koramit\LaravelLINEBot;

use Koramit\LaravelLINEBot\DTOs\Emoji;
use Koramit\LaravelLINEBot\Exceptions\InvalidEmojiIndexException;

class LINEMessageObject
{
    public array $messages = [];

    /**
     * @param  Emoji[]|null  $emojis
     * @return $this
     *
     * @throws InvalidEmojiIndexException
     */
    public function text(string $text, ?array $emojis = null): self
    {
        if (! $emojis) {
            $this->messages[] = ['type' => 'text', 'text' => $text];

            return $this;
        }

        $emojiArray = [];
        $offset = 0;
        foreach ($emojis as $emoji) {
            $index = strpos($text, '&', $offset);
            if ($index === false) {
                throw new InvalidEmojiIndexException;
            }
            $emoji->index = $index;
            $emojiArray[] = $emoji->toArray();
            $offset = $index + 1;
        }

        $this->messages[] = [
            'type' => 'text',
            'text' => $text,
            'emojis' => $emojiArray,
        ];

        return $this;
    }

    public function get(): array
    {
        return $this->messages;
    }
}
