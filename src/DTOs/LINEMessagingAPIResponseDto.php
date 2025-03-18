<?php

namespace Koramit\LaravelLINEBot\DTOs;

readonly class LINEMessagingAPIResponseDto
{
    public function __construct(
        public int $status,
        public ?string $lineRequestId,
        public array $data
    ) {}
}
