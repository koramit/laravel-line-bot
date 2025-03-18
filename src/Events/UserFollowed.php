<?php

namespace Koramit\LaravelLINEBot\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Koramit\LaravelLINEBot\DTOs\LINEEventDto;
use Koramit\LaravelLINEBot\Models\LINEBotChatLog;
use Koramit\LaravelLINEBot\Models\LINEUserProfile;

class UserFollowed implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public LINEEventDto $dto;

    public LINEUserProfile $profile;

    public LINEBotChatLog $log;

    public function __construct(
        LINEEventDto $dto,
        LINEUserProfile $profile,
        LINEBotChatLog $log
    ) {
        if ($dto->isUnblocked === true) {
            $profile->update(['unfollowed_at' => null]);
        }

        $this->dto = $dto;
        $this->profile = $profile;
        $this->log = $log;
    }
}
