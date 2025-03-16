<?php

namespace Koramit\LaravelLINEBot\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Koramit\LaravelLINEBot\Models\LINEUserProfile;

trait LINEUserProfileAware
{
    public function lineProfile(): BelongsTo
    {
        return $this->belongsTo(LINEUserProfile::class);
    }
}
