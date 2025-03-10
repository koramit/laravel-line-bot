<?php

namespace Koramit\LaravelLINEBot;

use Illuminate\Support\Str;
use Koramit\LaravelLINEBot\DTOs\Emoji;
use Koramit\LaravelLINEBot\DTOs\Mention;
use Koramit\LaravelLINEBot\Exceptions\InvalidEmojiIndexException;
use Koramit\LaravelLINEBot\Exceptions\InvalidSubstitutionKeyException;
use Koramit\LaravelLINEBot\Exceptions\InvalidSubstitutionTypeException;

class LINEMessageObject
{
    public array $messages = [];

    /**
     * @param  Emoji[]|null  $emojis
     *
     * @throws InvalidEmojiIndexException
     */
    public function text(string $text, ?array $emojis = null, ?string $quoteToken = null): self
    {
        $payload['type'] = 'text';
        $payload['text'] = $text;
        if ($quoteToken) {
            $payload['quoteToken'] = $quoteToken;
        }

        if (! $emojis) {
            $this->messages[] = $payload;

            return $this;
        }

        $emojiArray = [];
        $offset = 0;
        foreach ($emojis as $emoji) {
            $index = Str::position($text, '$', $offset);
            if ($index === false) {
                throw new InvalidEmojiIndexException;
            }
            $emoji->index = $index;
            $emojiArray[] = $emoji->toArray();
            $offset = $index + 1;
        }

        $payload['emojis'] = $emojiArray;
        $this->messages[] = $payload;

        return $this;
    }

    /**
     * @param  object[]  $substitutions
     *
     * @throws InvalidSubstitutionKeyException|InvalidSubstitutionTypeException
     */
    public function textV2(string $text, array $substitutions, ?string $quoteToken = null): self
    {
        preg_match_all('/(?<!\{)\{(?!\{)/', $text, $openCurlyBrackets);
        preg_match_all('/(?<!})}(?!})/', $text, $closeCurlyBrackets);
        if (
            count($substitutions) !== count($openCurlyBrackets[0])
            || count($substitutions) !== count($closeCurlyBrackets[0])
        ) {
            throw new InvalidSubstitutionKeyException;
        }

        $substitution = [];
        $offset = 0;
        foreach ($substitutions as $sub) {
            if (! in_array(get_class($sub), [Emoji::class, Mention::class])) {
                throw new InvalidSubstitutionTypeException;
            }

            $keyStartPos = Str::position($text, '{', $offset);
            $keyEndPos = Str::position($text, '}', $keyStartPos);
            if (! $keyStartPos || ! $keyEndPos) {
                throw new InvalidSubstitutionKeyException;
            }

            $key = Str::substr($text, ++$keyStartPos, $keyEndPos - $keyStartPos);
            $substitution[$key] = $sub->toSubstitution();
            $offset = $keyEndPos;
        }

        $payload = [
            'type' => 'textV2',
            'text' => $text,
            'substitution' => $substitution,
        ];

        if ($quoteToken) {
            $payload['quoteToken'] = $quoteToken;
        }
        $this->messages[] = $payload;

        return $this;
    }

    public function sticker(string $packageId, string $stickerId, ?string $quoteToken = null): self
    {
        $payload['type'] = 'sticker';
        $payload['packageId'] = $packageId;
        $payload['stickerId'] = $stickerId;
        if ($quoteToken) {
            $payload['quoteToken'] = $quoteToken;
        }
        $this->messages[] = $payload;

        return $this;
    }

    /**
     * @param  object[]  $actions
     */
    public function quickReply(array $actions): self
    {
        $quickReply['items'] = [];
        foreach ($actions as $action) {
            $quickReply['items'][] = $action->toArray();
        }
        $length = count($this->messages);
        $this->messages[$length - 1]['quickReply'] = $quickReply;

        return $this;
    }

    public function validate(): array
    {
        return (new LINEMessagingAPI)->validateMessageObject($this);
    }

    public function get(): array
    {
        return $this->messages;
    }
}
