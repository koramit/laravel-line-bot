<?php

namespace Koramit\LaravelLINEBot\DTOs;

use Koramit\LaravelLINEBot\Exceptions\UndefinedMentioneeUserIdException;

class Mention
{
    public function __construct(
        public string $type,
        public ?string $userId,
    ) {}

    /**
     * @throws UndefinedMentioneeUserIdException
     */
    public function toSubstitution(): array
    {
        if ($this->type === 'all') {
            return [
                'type' => 'mention',
                'mentionee' => ['type' => 'all'],
            ];
        }

        if (! $this->userId) {
            throw new UndefinedMentioneeUserIdException('Missing user id.');
        }

        return [
            'type' => 'mention',
            'mentionee' => [
                'type' => 'user',
                'userId' => $this->userId,
            ],
        ];
    }
}
