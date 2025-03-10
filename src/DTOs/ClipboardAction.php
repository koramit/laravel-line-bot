<?php

namespace Koramit\LaravelLINEBot\DTOs;

readonly class ClipboardAction
{
    public function __construct(
        public string $label,
        public string $clipboardText,
    ) {}

    public function toArray(): array
    {
        return [
            'type' => 'action',
            'action' => [
                'label' => $this->label,
                'clipboardText' => $this->clipboardText,
                'type' => 'clipboard',
            ],
        ];
    }
}
