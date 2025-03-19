<?php

namespace Koramit\LaravelLINEBot\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Koramit\LaravelLINEBot\Models\LINEBotChatLog;
use Koramit\LaravelLINEBot\Models\LINEUserProfile;

trait LINEUserProfileAware
{
    public function lineProfile(): HasOne
    {
        return $this->hasOne(LINEUserProfile::class);
    }

    public function chatLogs(): HasMany
    {
        return $this->hasMany(LINEBotChatLog::class);
    }
}
