<?php

namespace Koramit\LaravelLINEBot\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Koramit\LaravelLINEBot\Models\LINEUserProfile;

trait LINEUserProfileAware
{
    public function lineProfiles(): HasMany
    {
        return $this->hasMany(LINEUserProfile::class);
    }
}
