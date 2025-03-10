<?php

namespace Koramit\LaravelLINEBot\DTOs;

use Koramit\LaravelLINEBot\Enums\LINEActionInputOption;

class PostbackAction
{
    public function __construct(
        public string $label,
        public string $data,
        public ?string $displayText = null,
        public ?LINEActionInputOption $inputOption = null,
        public ?string $fillInText = null,
    ) {}

    public function toArray(): array
    {
        $action['type'] = 'postback';
        $action['label'] = $this->label;
        $action['data'] = $this->data;

        if ($this->displayText) {
            $action['displayText'] = $this->displayText;
        }

        if ($this->inputOption) {
            $action['inputOption'] = $this->inputOption->value;
        }

        if ($this->fillInText) {
            $action['fillInText'] = $this->fillInText;
        }

        return [
            'type' => 'action',
            'action' => $action,
        ];
    }
}
