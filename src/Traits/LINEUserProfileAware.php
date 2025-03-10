<?php

namespace Koramit\LaravelLINEBot\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Koramit\LaravelLINEBot\Models\LINEUserProfile;

trait LINEUserProfileAware
{
    public function lineProfiles(): HasMany
    {
        return $this->hasMany(LINEUserProfile::class);
    }

    public function activeLineProfile(): HasOne
    {
        return $this->hasOne(LINEUserProfile::class)
            ->ofMany(['updated_at' => 'max'], function (Builder $query) {
                $query->where('line_user_id', $this->line_user_id);
            });
    }
}
