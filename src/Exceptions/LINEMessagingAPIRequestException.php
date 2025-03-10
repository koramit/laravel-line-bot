<?php

namespace Koramit\LaravelLINEBot\Exceptions;

use Exception;

class LINEMessagingAPIRequestException extends Exception
{
    public array $body;

    public function __construct(string $message, int $code, array $body)
    {
        parent::__construct($message, $code);

        $this->body = $body;
    }
}
